<?php

namespace FTFS\NotificationBundle\Container\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use FTFS\NotificationBundle\Entity\EventLog;

/**
 *
 * listen event registration change
 *
 * trigger resource access log & notification process
 */
class EventRegistrationListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof EventLog) {
             //throw new \Exception('test');
            // notify all relatives by passing throw and catch filters
            /*
            $persist_log = true; // persist the notifications generated??
            $this->container->get('ftfs_notification.notifier.event_notification_notifier')
                            ->notify($entity, $persist_log);
             */
        }
    }
}
