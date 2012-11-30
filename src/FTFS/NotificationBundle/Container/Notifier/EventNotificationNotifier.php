<?php

namespace FTFS\NotificationBundle\Container\Notifier;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use FTFS\NotificationBundle\Container\Filter\EventCatchFilter;
use FTFS\NotificationBundle\Container\Sender\Sender;
use FTFS\ServiceBundle\Container\TicketTimer;

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
    private $timer;

    // notification list waiting persist
    // add to this array all new notifications
    private $notifications;

    public function __construct(EntityManager $entityManager, $templating, TranslatorInterface $translator, Router $router, EventCatchFilter $eventCatchFilter, Sender $sender, TicketTimer $timer)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->router = $router;
        $this->eventCatchFilter = $eventCatchFilter;
        $this->sender = $sender;
        $this->timer = $timer;

        $this->notifications = array();
    }

    /**
     *
     * throw the notifications
     */
    public function notify(EventLog $eventlog, $persist_log = true)
    {
        //throw new \Exception('test');
        // parsing the event to notifications
        $this->parseEventToNotifications($eventlog);

        // sending notifications
        $this->sender->send($this->notifications);
        //throw new \Exception(count($this->notifications));

        // if persist, persisting all notifications
        if($persist_log) {
            foreach($this->notifications as $notification) {
                $this->em->persist($notification);
            }
            //ToDo: eliminate this flushing ....
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
                $this->triggerTimer($eventlog, $service_ticket, $eventargs[2]);

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
                    case 'attachment_uploaded':
                    case 'attachment_deleteed':
                    case 'opened':
                    case 'closed':
                    case 'reopened':
                    case 'pended':
                    case 'continued':
                    case 'message_sended':
                    case 'intervention_added':
                    case 'logistics_added':
                        $this->registerNotifications($eventlog, $service_ticket->getRequestedBy());
                        if($service_ticket->getAssignedTo() && !$service_ticket->getAssignedTo()->isLocked()) {
                            $this->registerNotifications($eventlog, $service_ticket->getAssignedTo());
                        }
                        break;
                    // to owner only
                    case 'created':
                        $action = $eventlog->getAction();
                        switch($action['option']) {
                            // owner; responsible
                            case 'create.open':
                                //throw new \Exception('debuging');
                                if($service_ticket->getAssignedTo() && !$service_ticket->getAssignedTo()->isLocked()) {
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
     * trigger timer
     */
    public function triggerTimer(EventLog $eventlog, \FTFS\ServiceBundle\Entity\ServiceTicket $ticket, $event_type)
    {
        $quand = $eventlog->getActedAt();
        $qui = $eventlog->getActor();
        $quoi = '';
        $reason = '';
        //$ticket
        $flag = '';
        $alias = '';

        $trigger = true;

        switch($event_type) {
            case 'created':
                $action = $eventlog->getAction();
                if($action['option']=='create.submit') {
                    $quoi = 'ticket submitted'; 
                    $reason = $ticket->getSummary();
                    $flag = 'tic';
                    $alias = 'at_submit';
                }elseif($action['option']=='create.open') {
                    $quoi = 'ticket opened'; 
                    $reason = 'A ticket has been opened.';
                    $flag = 'tic';
                    $alias = 'at_open';
                }
                break;
            case 'submitted':
                    $quoi = 'ticket submitted'; 
                    $reason = $ticket->getSummary();
                    $flag = 'tic';
                    $alias = 'at_submit';
                break;
            case 'opened':
                    $quoi = 'ticket opened'; 
                    $reason = 'The ticket has been opened.';
                    $flag = 'tic';
                    $alias = 'at_open';
                break;
            default:
                $trigger = false;
        }
        if($trigger) {
            $this->timer->trigger($quand, $qui, $quoi, $reason, $ticket, $flag, $alias);
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
            // generate notification: txt_mini, txt, html
            $eventargs = $this->getEventArgs($eventlog);
            $service_ticket = $this->getSubject('serviceticket', $eventlog->getAction());
            $event_action = $eventlog->getAction();

            // notifications
            $notifications['txt'] = $this->templating->render('FTFSNotificationBundle:Message:event_'.$eventargs[1].
                '_'.$eventargs[2].'.txt.twig', array(
                    'subject' => $service_ticket,
                    'destinaire' => $notified_to,
                    'subject_href' => $this->router->generate(
                        'ftfs_dashboardbundle_myservice_show_by_name', 
                        array(
                            'name' => $event_action['serviceticket_name'],
                        ), 
                        true
                    ),
                    'actor' => $eventlog->getActor(),
                    'acted_at' => $eventlog->getActedAt()->format('Y-m-d H:i:s'),
                    'action' => $event_action,
                )
            );

            //throw new \Exception($eventargs[2].':'.$notifications['txt']);

            $notifications['html'] = $this->templating->render('FTFSNotificationBundle:Message:event_'.$eventargs[1].
                '_'.$eventargs[2].'.html.twig', array(
                    'subject' => $service_ticket,
                    'destinaire' => $notified_to,
                    'subject_href' => $this->router->generate(
                        'ftfs_dashboardbundle_myservice_show_by_name', 
                        array(
                            'name' => $event_action['serviceticket_name'],
                        ), 
                        true
                    ),
                    'actor' => $eventlog->getActor(),
                    'acted_at' => $eventlog->getActedAt()->format('Y-m-d H:i:s'),
                    'action' => $event_action,
                )
            );
            $message['notifications'] = $notifications;

            // attachments 
            $timer = $this->em->getRepository('FTFSServiceBundle:ServiceTicketTimer')->findByTicket($service_ticket->getName());

            $attachments[$service_ticket->getName().'.txt'] = array(
                'content' => $this->templating->render('FTFSNotificationBundle:Message:message_service_ticket_info.txt.twig', array(
                    'subject' => $service_ticket,
                    'timer' => $timer,
                )),
                'type' => 'text/plain',
            );
            $attachments[$service_ticket->getName().'.html'] = array(
                'content' => $this->templating->render('FTFSNotificationBundle:Message:message_service_ticket_info.html.twig', array(
                    'subject' => $service_ticket,
                    'timer' => $timer,
                )),
                'type' => 'text/html',
            );
            $message['attachments'] = $attachments;
            //throw new \Exception(print_r($attachments));

            // emails
            $emails['txt'] = $this->templating->render('FTFSNotificationBundle:Message:notification_email_layout.txt.twig', array(
                    'destinaire' => $notified_to,
                    'message_body' => $notifications['txt'],
                    'message_attachment' => $attachments[$service_ticket->getName().'.txt']['content'],
            ));
            $emails['html'] = $this->templating->render('FTFSNotificationBundle:Message:notification_email_layout.html.twig', array(
                    'destinaire' => $notified_to,
                    'message_body' => $notifications['html'],
                    'message_attachment' => $attachments[$service_ticket->getName().'.html']['content'],
            ));
            //throw new \Exception($emails['html']);
            $message['emails'] = $emails;

            // 
            $methods = $this->eventCatchFilter->getNotificationMethods($notified_to, $eventlog->getEvent(), $notified_to === $eventlog->getActor());
            $notifications = array();
            foreach($methods as $method) {
                $notifications[] = $this->generateNotificationLog($eventlog, $notified_to, $method, $message);
            }
            $this->notifications = array_merge($this->notifications, $notifications);
            
        }
    }

    /**
     *
     * generate a notification log
     */
    protected function generateNotificationLog(EventLog $eventlog, UserInterface $notified_to, $method = null, array $message)
    {
        $notificationlog = new \FTFS\NotificationBundle\Entity\NotificationLog();
        $notificationlog->setEvent($eventlog->getEvent());

        // generate message realted: method, format, message, cc
        //$format = $format ? $format : 'txt';        // set default message format, method
        $method = $method ? $method : $this->getDefaultNotificationMethod();
        $notificationlog->setMethod($method);

        $notificationlog->setCc(null);              // by default, null

        $eventargs = $this->getEventArgs($eventlog);
        $event_action = $eventlog->getAction();
        // set destinaire **************
        $notificationlog->setNotifiedTo($notified_to);
        // editing cc field
        // check if $service_ticket or $action has a cc 
        $service_ticket = $this->getSubject('serviceticket', $event_action);

        $notificationlog->setMiniMessage(trim($message['notifications']['txt']));

        switch($method->getName()) {
            case 'system':
                $notificationlog->setHtmlMessage(trim($message['notifications']['html']));
                break;
            case 'email':
                if($notified_to == $service_ticket->getRequestedBy()) {
                    $notificationlog->setCc($service_ticket->getShareList());
                }
                $notificationlog->setHtmlMessage(trim($message['emails']['html']));
                $notificationlog->setTextMessage(trim($message['emails']['txt']));
                if(array_key_exists('attachments', $message)) {
                    $notificationlog->setAttachments($message['attachments']);
                }
                break;
            case 'sms':
                break;
        }
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
            array_key_exists($subject.'_name', $action)) {
                $entity = $this->em->getRepository($action[$subject.'_class'])
                    ->findOneBy(array('name' => $action[$subject.'_name']));
                if($entity) {
                    return $entity;
                }else{
                    return $this->getNewTicket($subject, $action);
                }
        }
        return null;
    }

    protected function getNewTicket($subject, array $action)
    {
        $entity = new \FTFS\ServiceBundle\Entity\ServiceTicket;
        $entity->setName($action['serviceticket_name']);
        $entity->setStatus($action['serviceticket_status']);
        $entity->setLastModifiedAt($action['serviceticket_last_modified_at']);
        $entity->setRequestedAt($action['serviceticket_requested_at']);
        $entity->setRequestedBy($this->em->getRepository('FTFSUserBundle:User')->find($action['serviceticket_requested_by']));
        $user = $this->em->getRepository('FTFSUserBundle:User')->find($action['serviceticket_assigned_to']);
        if($user) {
            $entity->setAssignedTo($user);
        }
        $entity->setSummary($action['serviceticket_summary']);
        $entity->setDetail($action['serviceticket_detail']);
        $entity->setService($action['serviceticket_service']);
        $entity->setSeverity($action['serviceticket_severity']);
        $entity->setPriority($action['serviceticket_priority']);
        return $entity;
    }
}
