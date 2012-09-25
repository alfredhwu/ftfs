<?php

namespace FTFS\NotificationBundle\Container\Notifier;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use FTFS\NotificationBundle\Container\Filter\EventCatchFilter;
use FTFS\NotificationBundle\Container\Sender\Sender;

use DateTime;
use Symfony\Component\Security\Core\User\UserInterface;
use FTFS\NotificationBundle\Entity\EventLog;

/**
 * event notify 
 */
class EventNotificationNotifier
{
    private $em;
    private $translator;
    private $templating;
    private $eventCatchFilter;
    private $sender;

    // notification list waiting persist
    // add to this array all new notifications
    private $notifications;

    public function __construct(EntityManager $entityManager, $templating, TranslatorInterface $translator, EventCatchFilter $eventCatchFilter, Sender $sender)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->eventCatchFilter = $eventCatchFilter;
        $this->sender = $sender;

        $this->notifications = array();
    }

    /**
     *
     * throw the notifications
     */
    public function notify(EventLog $eventlog, $persist_log = true)
    {
        // parsing the event to notifications
        $this->parseEventToNotifications($eventlog);

        // sending notifications
        $this->sender->send($this->notifications);

  //      throw new \Exception('debugging...');
        // if persist, persisting all notifications
        if($persist_log) {
            foreach($this->notifications as $notification) {
                $this->em->persist($notification);
            }
            $this->em->flush();
        }
    }

    protected function parseEventToNotifications(Eventlog $eventlog)
    {
        // according to diff event type, throw event notfication request
        // para: $security_level, $eventargs
        $security_level = $eventlog->getEvent()->getSecurityLevel();
        $eventargs = $this->getEventArgs($eventlog);
        switch($eventargs[1]) {
            case 'serviceticket':
                // notify both client owner and assigned agent
                switch($eventargs[2]) {
                    case 'update':
                        if(!array_key_exists('change_set', $eventlog->getAction())) {
                            break;
                        }
                    default:
                        $service_ticket = $this->getSubject('serviceticket', $eventlog->getAction());
                        $this->registerNotifications($eventlog, $service_ticket->getRequestedBy());
                        $this->registerNotifications($eventlog, $service_ticket->getAssignedTo());
                }
                break;
            default:
                throw new \Exception('Unknown event "'.$eventkey.'"');
        }
    }

    /**
     *
     * catch the notifications and register into the notification log
     */
    protected function registerNotifications(EventLog $eventlog, UserInterface $notified_to)
    {
        if($notified_to) {
            $filters = $this->eventCatchFilter->getNotificationMethod($notified_to, $eventlog->getEvent());
            $notifications = array();
            foreach($filters as $filter) {
                $method = $filter->getMethod();
                $notifications[] = $this->generateNotificationLog($eventlog, $notified_to, $method);
            }
            $this->notifications = array_merge($this->notifications, $notifications);
        }
    }

    /**
     *
     * generate a notification log
     */
    protected function generateNotificationLog(EventLog $eventlog, UserInterface $notified_to, $method = null, $format = null)
    {
        $notificationlog = new \FTFS\NotificationBundle\Entity\NotificationLog();
        $notificationlog->setEvent($eventlog->getEvent());

        // generate message realted: method, format, message, cc
        $format = $format ? $format : 'txt';        // set default message format, method
        $method = $method ? $method : $this->getDefaultNotificationMethod();
        if($method->getName()==='email') {
            $format = 'html';
        }
        //
        $notificationlog->setMethod($method);
        $notificationlog->setCc(null);              // by default, null

        $eventargs = $this->getEventArgs($eventlog);
        switch($eventargs[1]) {
            case 'serviceticket':
                $service_ticket = $this->getSubject('serviceticket', $eventlog->getAction());
                $action = $eventlog->getAction();
                if(array_key_exists('change_set', $action)) {
                    $change_set = $action['change_set'];
                }else{
                    $change_set = null;
                }
                if(array_key_exists('attachment_name', $action)) {
                    $attachment_name = $action['attachment_name'];
                }else{
                    $attachment_name = null;
                }
                if(array_key_exists('observation', $action)) {
                    $observation = $action['observation'];
                }else{
                    $observation = null;
                }
                if(array_key_exists('observation_to', $action)) {
                    $observation_to = $action['observation_to'];
                }else{
                    $observation_to = null;
                }
                // set destinaire **************
                $notificationlog->setNotifiedTo($notified_to);
                if($method->getName() === 'email') {
                    // editing cc field
                    // check if $service_ticket or $action has a cc 
                    $notificationlog->setCc(null);
                }
                /*
                $message = $this->translator->trans($eventlog->getEvent()->getEventKey().'.action.'.$method->getName().'.'.$format, array(
                    '%service_ticket%' => $service_ticket->getName(),
                    '%visited_by%' => $eventlog->getActor(),
                    '%visited_at%' => $eventlog->getActedAt()->format('Y-m-d H:i:s'),
                    '%change_set%' => $change_set,
                ), 'notification');
                 */
                $message = $this->templating
                    ->render('FTFSNotificationBundle:Message:message_service_ticket.'.$format.'.twig', array(
                    'method' => $method->getName(),
                    'action' => $eventlog->getEvent()->getEventKey(),
                    'notified_to' => $notified_to,
                    'service_ticket' => $service_ticket->getName(),
                    'visited_by' => $eventlog->getActor(),
                    'visited_at' => $eventlog->getActedAt()->format('Y-m-d H:i:s'),
                    'change_set' => $change_set,
                    'attachment_name' => $attachment_name,
                    'observation' => $observation,
                    'observation_to' => $observation_to,
                ));
                throw new \Exception($message); // Todo Debugging >>>
                break;
            default:
                $message = $this->translator->trans($eventlog->getEvent()->getEventKey().'.action.default.txt', array(
                    '%event_key%' => $eventlog->getEvent()->getEventKey(),
                    '%acted_by%' => $eventlog->getActor(),
                    '%acted_at%' => $eventlog->getActedAt()->format('Y-m-d H:i:s'),
                ), 'notification');
        }
        // debuging : check rendered message
        // throw new \Exception('test rendering:'.$message);
        $notificationlog->setMessage($message);
        $notificationlog->setNotifiedAt(null);

        return $notificationlog;
    }

    /**
     *
     * Get default notification method
     */
    protected function getDefaultNotificationMethod()
    {
        // Todo: edit this in syst config file
        $default_method_name = 'system';
        $method = $this->em->getRepository('FTFSNotificationBundle:NotificationMethod')
                    ->findOneByName($default_method_name);
        if(!$method) {
            throw new \Exception('Error: default method named '.$default_method_name.' can not be found ! Please contact the developper. (caused probably by delete of an entry in ftfs_notification_notification_method with key name = '.$default_method_name.')');
        }
        return $method;
    }

    /**
     *
     * parsing event.key => event.args
     */
    protected function getEventArgs(EventLog $eventlog)
    {
        $eventkey = $eventlog->getEvent()->getEventKey();
        $eventargs = preg_split('/\./', $eventkey);
        // $eventargs[0] === 'event'
        // $eventargs[1]:   class
        // $eventargs[2]:   action
        if(count($eventargs)<=2 or $eventargs[0]!=='event') {
            throw new \Exception('Unknown event key "'.$eventkey.'"');
        }
        return $eventargs;
    }

    /**
     *
     * Extract subject classes form $action parameters of $eventlog
     */
    protected function getSubject($subject, array $action)
    {
        if(array_key_exists($subject.'_class', $action) && 
            array_key_exists($subject.'_id', $action)) {
            return $this->em->find($action[$subject.'_class'], $action[$subject.'_id']);
        }
        return null;
    }
}
