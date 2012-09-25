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
        $action['serviceticket_id'] = $entity->getId();
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
            $action = $this->generateAction('create', $entity, $entityManager);
            switch($entity->getStatus()) {
                case 'submitted':
                    $option = 'create.submit';
                    break;
                case 'opened':
                    $option = 'create.open';
                    break;
                default:
                    $option = null;
            }
            if($option) {
                $action['option']=$option;
                $this->notify('event.serviceticket.create', $action);
            }
        }

        if($entity instanceof ServiceTicketAttachment) {
            $action = $this->generateAction('attachment_upload', $entity->getTicket(), $entityManager);
            $action['attachment_name'] = $entity->getName(); 
            $this->notify('event.serviceticket.attachment_upload', $action);
        }

        if($entity instanceof ServiceTicketObservation) {
            //throw new \Exception('coucou');
            $action = $this->generateAction('observation_add', $entity->getTicket(), $entityManager);
            $action['observation'] = $entity->getContent(); 
            if($entity->getAttachTo()) {
                $action['observation_to'] = $entity->getAttachTo()->getSendBy();
            }else{
                $action['observation_to'] = null;
            }
            $this->notify('event.serviceticket.observation_add', $action);
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
                // treatement of change_set
                $action = $this->generateAction('update', $entity, $entityManager);
                $action['change_set'] = $session->get('change_set['.$entity->getName().']');
                $this->notify('event.serviceticket.update', $action);
                $session->remove('change_set['.$entity->getName().']');
            }
        }
    }
}
