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
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notification, $subject=null)
    {
        $destinaire = $notification->getNotifiedTo();
        if(!$destinaire) {
            throw new \Exception('Destinaire must be set for any notification');
        }
        $attachments = array();
        foreach($notification->getAttachments() as $name => $attachment){
            $attachments[] = \Swift_Attachment::newInstance($attachment['content'], $name, $attachment['type']);
        };
        $this->ftfs_mailer->send(array(
            'subject' => $subject,
            //'from' => 'blabla',
            'to' => array($notification->getNotifiedTo()->getEmail() => $notification->getNotifiedTo()),
            'cc' => $notification->getCc(),
            'body_html' => $notification->getHtmlMessage(),
            'body_txt' => $notification->getTxtMessage(),
            'attachments' => $attachments,
        ));

        /*
        if(array_key_exists('attachment_name', $message_options)) {
            $attachment_name = $message_options['attachment_name'];
        }else{
            $attachment_name = 'attachment';
        }
        if(array_key_exists('attachment_name', $message_options)) {
            $attachment_html = \Swift_Attachment::newInstance($message_options['body_html'], $attachment_name.'.html', 'text/html');
            $attachment_txt = \Swift_Attachment::newInstance($message_options['body_txt'], $attachment_name.'.txt', 'text/txt');
         */
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
