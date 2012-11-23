<?php

namespace FTFS\NotificationBundle\Container\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FTFS\ServiceBundle\Entity\ServiceTicket;
use FTFS\ServiceBundle\Entity\ServiceTicketAttachment;
use FTFS\ServiceBundle\Entity\ServiceTicketObservation;

/**
 *
 * listen service ticket change event
 */
class ServiceTicketListener
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

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
            $action = $this->generateAction('created', $entity, $entityManager);
            switch($entity->getStatus()) {
                case 'submitted':
                    $option = 'create.submit';
                    break;
                case 'opened':
                    $option = 'create.open';
                    break;
                default:
                    $option = 'create.only';
            }
            if($option) {
                $action['option']=$option;
                $action['serviceticket_status']=$entity->getStatus();
                $action['serviceticket_last_modified_at']=$entity->getLastModifiedAt();
                $action['serviceticket_requested_at']=$entity->getRequestedAt();
                $action['serviceticket_requested_by']=$entity->getRequestedBy()->getId();
                $action['serviceticket_summary']=$entity->getSummary();
                $action['serviceticket_detail']=$entity->getDetail();
                $action['serviceticket_service']=$entity->getService();
                $action['serviceticket_severity']=$entity->getSeverity();
                $action['serviceticket_priority']=$entity->getPriority();
                $action['serviceticket_assigned_to']=$entity->getAssignedTo()?$entity->getAssignedTo()->getId():-1;
                $action_name = $action['name'];
                $this->notify('event.serviceticket.'.$action_name, $action);
            }
        }

        if($entity instanceof ServiceTicketAttachment) {
            $action = $this->generateAction('attachment_uploaded', $entity->getTicket(), $entityManager);
            $action_name = $action['name'];
            $action['attachment_name'] = $entity->getName(); 
            $this->notify('event.serviceticket.'.$action_name, $action);
        }

        if($entity instanceof ServiceTicketObservation) {
            //throw new \Exception('coucou');
            $action = $this->generateAction('observation_added', $entity->getTicket(), $entityManager);
            $action['observation'] = $entity->getContent(); 
            if($entity->getAttachTo()) {
                $action['observation_to'] = $entity->getAttachTo()->getSendBy();
            }else{
                $action['observation_to'] = null;
            }
            $action_name = $action['name'];
            $this->notify('event.serviceticket.'.$action_name, $action);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
            $session =  $this->container->get('session');
            // and save change_set 
            $change_set = array();
            foreach($args->getEntityChangeset() as $fieldname => $fieldchange){
                if(substr($fieldname, -3)!=='_at' && $fieldchange[0] != $fieldchange[1]) {    // filter out timestamp changes
                    $change_set[$fieldname] = $fieldchange;
                }
            }
            if(count($change_set)>0) {
                $session->set('change_set['.$entity->getName().']', $change_set);
            }elseif($session->has('change_set['.$entity->getName().']')) {
                $session->remove('change_set['.$entity->getName().']');
            }
            $test = $session->get('change_set['.$entity->getName().']');
 //           throw new \Exception($test['service'][1]);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
            $session =  $this->container->get('session');
            if($session->has('change_set['.$entity->getName().']')) {
                $change_set = $session->get('change_set['.$entity->getName().']');
                // treatement of change_set
                //throw new \Exception(print_r(array_keys($change_set)));

                
                if(array_key_exists('status', $change_set)){
                    $action = $this->generateAction($change_set['status'][1], $entity, $entityManager);
                }elseif(array_key_exists('assigned_to', $change_set)){
                    $action = $this->generateAction('reassigned', $entity, $entityManager);
                }else{
                    $action = $this->generateAction('updated', $entity, $entityManager);
                }
                $action_name = $action['name'];
                $action['change_set'] = $change_set;
                $this->notify('event.serviceticket.'.$action_name, $action);
                $session->remove('change_set['.$entity->getName().']');
            }
        }
    }
}
