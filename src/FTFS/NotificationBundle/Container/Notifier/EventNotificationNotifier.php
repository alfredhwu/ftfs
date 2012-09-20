<?php

namespace FTFS\NotificationBundle\Container\Notifier;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use DateTime;
use FTFS\NotificationBundle\Entity\EventLog;

/**
 * event notify 
 */
class EventNotificationNotifier
{
    private $em;
    private $templating;

    public function __construct(EntityManager $entityManager, EngineInterface $templating)
    {
        $this->em = $entityManager;
        $this->templating = $templating;
    }

    /**
     *
     * throw the notifications
     */
    public function notify(EventLog $eventlog)
    {
        // write notification log
        $security_level = $eventlog->getEvent()->getSecurityLevel();
        $event_key = $eventlog->getEvent()->getEventKey();

        // ToDo: define filters
        $user_filter = null;

        $eventargs = $this->getEventArgs($eventlog);
        switch($eventargs[1]) {
            case 'serviceticket':
                $service_ticket = $this->getSubject('serviceticket', $eventlog->getAction());
                $this->register($eventlog, $service_ticket->getRequestedBy(), $user_filter);
                $this->register($eventlog, $service_ticket->getAssignedTo(), $user_filter);
                break;
            default:
                throw new \Exception('Unknown event "'.$eventkey.'"');
        }
        // flush the registrations of notifications
        // declenche the sender process via listner
        $this->em->flush();
    }

    /**
     *
     * catch the notifications
     */
    protected function register(EventLog $eventlog, UserInterface $notified_to, $user_filter)
    {
        if($notified_to) {
            $this->em->persist($this->generateNotificationLog($eventlog, $notified_to));
        }
    }

    /**
     *
     * writing notification log
     */
    protected function generateNotificationLog(EventLog $eventlog, UserInterface $notified_to, $method = null, $format = null)
    {
        $notificationlog = new \FTFS\NotificationBundle\Entity\NotificationLog();
        $notificationlog->setEvent($eventlog->getEvent());

        // generate message realted: method, format, message, cc
        $format = $format ? $format : 'txt';        // set default message format, method
        $method = $method ? $method : 'system';
        //
        $notificationlog->setMethod($method);
        $notificationlog->setCc(null);              // by default, null

        $eventargs = $this->getEventArgs($eventlog);
        switch($eventargs[1]) {
            case 'serviceticket':
                $service_ticket = $this->getSubject('serviceticket', $eventlog->getAction());
                // set destinaire **************
                $notificationlog->setNotifiedTo($notified_to);
                if($method === 'email') {
                    // editing cc field
                    // check if $service_ticket or $action has a cc 
                    $notificationlog->setCc(null);
                }
                $message = $this->templating->render('FTFSNotificationBundle:NotificationMessage:'.$eventlog->getEvent()->getEventKey().'.'.$method.'.'.$format.'.twig', array(
                    'eventlog' => $eventlog,
                    'notificationlog' => $notificationlog,
                    'subject' => $service_ticket,
                ));
                break;
            default:
                $message = $this->templating->render('FTFSNotificationBundle:NotificationMessage:event.default.system.txt.twig', array(
                    'eventlog' => $eventlog,
                    'notificationlog' => $notificationlog,
                ));
        }
        // debuging : check rendered message
        // throw new \Exception('test rendering:'.$message);
        $notificationlog->setMessage($message);
        $notificationlog->setNotifiedAt(null);

        return $notificationlog;
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
