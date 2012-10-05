<?php

namespace FTFS\NotificationBundle\Container\Notifier;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
    private $router;
    private $templating;
    private $eventCatchFilter;
    private $sender;

    // notification list waiting persist
    // add to this array all new notifications
    private $notifications;

    public function __construct(EntityManager $entityManager, $templating, TranslatorInterface $translator, Router $router, EventCatchFilter $eventCatchFilter, Sender $sender)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->router = $router;
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
        $eventkey = $eventlog->getEvent()->getEventKey();
        $eventargs = $this->getEventArgs($eventlog);
        switch($eventargs[1]) {
            case 'serviceticket':
                $service_ticket = $this->getSubject('serviceticket', $eventlog->getAction());
                // notify both client owner and assigned agent
                switch($eventargs[2]) {
                    // ticket assigning message, only to group agent by default
                    case 'assigned':
                    case 'reassigned':
                        $this->notifyGroupMembers($eventlog);
                        break;
                    // owner; responsible agent
                    case 'updated':
                        if(!array_key_exists('change_set', $eventlog->getAction())) {
                            break;
                        }
                    case 'opened':
                    case 'closed':
                    case 'attachment_uploaded':
                    case 'attachment_deleteed':
                    case 'observation_added':
                        $this->registerNotifications($eventlog, $service_ticket->getRequestedBy());
                        if($service_ticket->getAssignedTo()) {
                            $this->registerNotifications($eventlog, $service_ticket->getAssignedTo());
                        }
                        break;
                    // to owner only
                    case 'created':
                        $action = $eventlog->getAction();
                        switch($action['option']) {
                            // owner; responsible
                            case 'create.open':
                                if($service_ticket->getAssignedTo()) {
                                    $this->registerNotifications($eventlog, $service_ticket->getAssignedTo());
                                }
                                $this->registerNotifications($eventlog, $service_ticket->getRequestedBy());
                                break;
                            // owner; all agent
                            case 'create.submit':
                                $this->notifyGroupMembers($eventlog);
                                $this->registerNotifications($eventlog, $service_ticket->getRequestedBy());
                                break;
                            // no notification
                            default:
                        }
                        break;
                    // owner; all agents
                    case 'submitted':
                        $this->registerNotifications($eventlog, $service_ticket->getRequestedBy());
                        $this->notifyGroupMembers($eventlog);
                        break;
                    default:
                        throw new \Exception('Unknown event "'.$eventkey.'"');
                }
                break;
            default:
                throw new \Exception('Unknown event "'.$eventkey.'"');
        }
    }

    /**
     *
     * notify all group agents
     */
    protected function notifyGroupMembers(EventLog $eventlog, $group=null)
    {
        $users = $this->em->getRepository('FTFSUserBundle:User')->findAll();
        $agents = array();
        foreach($users as $user) {
            if($user->hasRole('ROLE_AGENT')) {
                $agents[] = $user;
            }
        }
        //throw new \Exception(count($agents));
        foreach($agents as $agent) {
            $this->registerNotifications($eventlog, $agent);
        }
    }

    /**
     *
     * catch the notifications and register into the notification log
     */
    protected function registerNotifications(EventLog $eventlog, UserInterface $notified_to)
    {
        if($notified_to) {
            $methods = $this->eventCatchFilter->getNotificationMethods($notified_to, $eventlog->getEvent(), $notified_to === $eventlog->getActor());
            $notifications = array();
            foreach($methods as $method) {
                switch($method->getName()) {
                    case 'system':
                    case 'email':
                        $notifications[] = $this->generateNotificationLog($eventlog, $notified_to, $method, 'html');
                        break;
                    case 'sms':
                        if(!$notified_to->getMobilePhone()){
                            break;
                        }
                    default:
                        $notifications[] = $this->generateNotificationLog($eventlog, $notified_to, $method);
                }
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
        //
        $notificationlog->setMethod($method);
        $notificationlog->setCc(null);              // by default, null

        $eventargs = $this->getEventArgs($eventlog);
        $event_action = $eventlog->getAction();
        // set destinaire **************
        $notificationlog->setNotifiedTo($notified_to);
        // editing cc field
        // check if $service_ticket or $action has a cc 
        $service_ticket = $this->getSubject('serviceticket', $event_action);
        if($method->getName() === 'email') {
            $notificationlog->setCc($service_ticket->getShareList());
        }
        $message = $this->templating->render('FTFSNotificationBundle:Message:event_'.$eventargs[1].
                '_'.$eventargs[2].'.'.$format.'.twig', array(
            'method' => $method->getName(),
            'destinaire' => $notified_to,
            'subject' => $service_ticket,
            'subject_href' => $this->router->generate(
                'ftfs_dashboardbundle_myservice_show', 
                array(
                    'id' => $this->getSubject('serviceticket', $event_action)->getId(),
                ), 
                true
            ),
            'actor' => $eventlog->getActor(),
            'acted_at' => $eventlog->getActedAt()->format('Y-m-d H:i:s'),
            'action' => $event_action,
        ));
        // debuging : check rendered message
        //throw new \Exception('test rendering:'.$message);// Todo Debugging >>>
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
