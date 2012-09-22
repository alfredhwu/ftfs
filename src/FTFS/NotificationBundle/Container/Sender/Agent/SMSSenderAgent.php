<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

/**
 * An agent that will send notifications through sms.
 *
 */
class SMSSenderAgent implements SenderAgentInterface
{
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notificationlog) {
    }

    public function sendBulk(array $notifications) {
    }
}
