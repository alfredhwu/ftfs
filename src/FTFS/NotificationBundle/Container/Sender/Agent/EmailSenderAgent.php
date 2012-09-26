<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

use FTFS\MailerBundle\Container\FTFSMailer;

/**
 * An agent that will send notifications through SwiftMailer.
 *
 */
class EmailSenderAgent implements SenderAgentInterface
{
    private $ftfs_mailer;

    public function __construct(FTFSMailer $ftfs_mailer)
    {
        $this->ftfs_mailer = $ftfs_mailer;
    }

    /**
     * Sends a single notification.
     *
     * @param \FTFS\NotificationBundle\Entity\NotificationLog $notificationlog
     */
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notification)
    {
        $destinaire = $notification->getNotifiedTo();
        if(!$destinaire) {
            throw new \Exception('Destinaire must be set for any notification');
        }
        $this->ftfs_mailer->send(array(
            //'subject' => 'blabla',
            //'from' => 'blabla',
            'to' => array($notification->getNotifiedTo()->getEmail() => $notification->getNotifiedTo()),
            'cc' => $notification->getCc(),
            'body_html' => $notification->getMessage(),
        ));
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
