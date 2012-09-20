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

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof EventLog) {
            // use user filter to send notifications
            $this->container->get('ftfs_notification.notifier.event_notification_notifier')->notify($entity);
        }
    }
}
