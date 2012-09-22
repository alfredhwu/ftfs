<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

/**
 * An agent that will send notifications through system notification.
 *
 */
class SystemSenderAgent implements SenderAgentInterface
{
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notificationlog) {
    }

    public function sendBulk(array $notifications) {
    }
}
