<?php

namespace FTFS\NotificationBundle\Container\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FTFS\ServiceBundle\Entity\ServiceTicket;

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
        $action['serviceticket_id'] = $metadata->getIdentifierValues($entity);
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

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof ServiceTicket) {
            $session =  $this->container->get('session');
            // and save change_set 
            $change_set = array();
            foreach($args->getEntityChangeset() as $fieldname => $fieldchange){
                if(substr($fieldname, -3)!=='_at') {    // filter out timestamp changes
                    $change_set[$fieldname] = $fieldchange;
                }
            }
            $session->set('change_set['.$entity->getName().']', $change_set);
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
