<?php

namespace FTFS\ServiceBundle\Container\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use FTFS\ServiceBundle\Entity\ServiceTicketObservation;

/**
 * 
 **/
class ServiceTicketObservationListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    private function notify($event_key, array $action)
    {
        // notify all relatives by passing throw and catch filters
        $current_user = $this->container->get('security.context')->getToken()->getUser();
        $this->container->get('ftfs_notification.notifier.event_registration_notifier')
            ->register($event_key, $current_user, $action);
    }

    private function generateAction($action_name, $entity, $entityManager)
    {
        $action['name'] = $action_name;
        $metadata = $entityManager->getClassMetadata(get_class($entity));
        $action['serviceticket_class'] = $metadata->getName();
        //$action['serviceticket_id'] = $metadata->getIdentifierValues($entity);
        //$action['serviceticket_id'] = $entity->getName();
        $action['serviceticket_name'] = $entity->getName();
        //throw new \Exception($entity->getName());
        return $action;
    }


    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicketObservation) {
            // if timer trigger observation
            $content = $entity->getContent();
            if(array_key_exists('timer', $content)) {
                $event_type = $content['timer'];
                $this->triggerTimer($event_type, $entity);
            }

            $content = $entity->getContent();
            $type = $content['type'];

            $ignore = false;
            switch($type) {
                case 'message':
                    $action_name = 'message_sended';
                    break;
                case 'intervention':
                    $action_name = 'intervention_added';
                    break;
                case 'logistics':
                    $action_name = 'logistics_added';
                    break;
                case 'pend':
                    $action_name = 'pended';
                    break;
                case 'continue':
                    $action_name = 'continued';
                    break;
                case 'close_report':
                    $action_name = 'closed';
                    break;
                case 'reopen':
                    $action_name = 'reopened';
                    break;
                default:
                    $ignore = true;
            }

            if(!$ignore) {
                // create fictif ticket
                $action = $this->generateAction($action_name, $entity->getTicket(), $entityManager);
                $action['observation'] = $entity->getContent(); 
                if($entity->getAttachTo()) {
                    $action['observation_to'] = $entity->getAttachTo()->getSendBy();
                }else{
                    $action['observation_to'] = null;
                }
                //throw new \Exception($type);
                $this->notify('event.serviceticket.'.$action_name, $action);
            }
        }
    }

    /**
     * trigger timer
     */
    public function triggerTimer($event_type, ServiceTicketObservation $observation)
    {
        $timer = $this->container->get("ftfs_servicebundle.ticket_timer");
        $content = $observation->getContent();
        //throw new \Exception(print_r($content));
        if(!array_key_exists('timer', $content)) {
            throw new \Exception('Internal Error !');
        }

        $quand = $observation->getSendAt();
        $qui =   $observation->getSendBy();
        $quoi = '';
        $ticket = $observation->getTicket();
        $flag = '';
        $alias = '';

        $trigger = true;

        switch($event_type) {
            case 'reopened':
                $quoi = 'ticket reopened';
                $reason = $content['reason'];
                $flag = '';
                $alias = 'at_reopen';
                break;
            case 'pended':
                $quoi = 'ticket pended';
                $reason = $content['reason'];
                $flag = 'tac';
                $alias = 'at_pend';
                break;
            case 'continued':
                $quoi = 'ticket continued';
                $reason = $content['reason'];
                $flag = 'tic';
                $alias = 'at_continue';
                break;
            case 'closed':
                $quoi = 'ticket closed';
                $reason = 'We have closed your ticket. Please refer to the close report.';
                $flag = 'tac';
                $alias = 'at_close';
                break;
            case 'intervention_added':
                $reason = $content['report'];
                $flag = '';
                if($content['category']==='telephone') {
                    $quoi = 'telephone investigation added';
                    $alias = 'at_investigation_telephone';
                }else{
                    $quoi = 'in site investigation added';
                    $alias = 'at_investigation_in_site';
                }
                break;
            default:
                $trigger = false;
        }
        if($trigger) {
            $timer->trigger($quand, $qui, $quoi, $reason, $ticket, $flag, $alias);
        }
    }
}
