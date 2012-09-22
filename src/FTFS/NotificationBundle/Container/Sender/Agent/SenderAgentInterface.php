<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

/**
 * Interface that represents a service that will take notifications
 * and send them by a specific method, like System, SMS or Email.
 */
interface SenderAgentInterface
{
    /**
     * Sends a single notification.
     *
     * @param \FTFS\NotificationBundle\Entity\NotificationLog $notificationlog
     */
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notificationlog);

    /**
     * Sends a group of notifications.
     *
     * @param array $notifications
     */
    public function sendBulk(array $notifications);
}
