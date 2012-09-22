<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

/**
 * An agent that will send notifications through SwiftMailer.
 *
 */
class EmailSenderAgent implements SenderAgentInterface
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends a single notification.
     *
     * @param \FTFS\NotificationBundle\Entity\NotificationLog $notificationlog
     */
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notification)
    {
        /** @var \Swift_Message $message  */
        $message = $this->mailer->createMessage();
        $message->setSubject('System Notification');
        $message->addPart($notification->getMessage(), 'text/plain', 'UTF8');

        $message->addTo($notification->getNotifiedTo()->getEmail(), $notification->getNotifiedTo());
        $message->setFrom('support@fujitsu-telecom.fr', 'FTFS Support Service Team');

        $this->mailer->send($message);
        $notification->setNotifiedAt(new \DateTime('now'));
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
