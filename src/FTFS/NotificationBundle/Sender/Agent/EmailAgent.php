<?php

/*
 * This file is part of the merkNotificationBundle package.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FTFS\NotificationBundle\Sender\Agent;

use merk\NotificationBundle\Model\NotificationInterface;
use merk\NotificationBundle\Sender\Agent\AgentInterface;

/**
 * An agent that will send notifications through SwiftMailer.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class EmailAgent implements AgentInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends a single notification.
     *
     * @param \merk\NotificationBundle\Model\NotificationInterface $notification
     */
    public function send(NotificationInterface $notification)
    {
        /** @var \Swift_Message $message  */
        $message = $this->mailer->createMessage();

        $message->setSubject($notification->getSubject());
        $message->addPart($notification->getMessage(), 'text/plain', 'UTF8');

        $message->addTo($notification->getRecipientData(), $notification->getRecipientName());
        $message->setFrom('noreply@fujitsu-telecom.fr', 'Support Service - Fujitsu Telcom France SAS');
        $message->addCc('alfredhwu@gmail.com');
        $message->addTo('dingwen.wu@telecom-paristech.fr');

        $this->mailer->send($message);
    }

    /**
     * Sends a group of notifications.
     *
     * @param array $notifications
     */
    public function sendBulk(array $notifications)
    {
        foreach ($notifications as $notification) {
            $this->send($notification);
        }
    }
}
