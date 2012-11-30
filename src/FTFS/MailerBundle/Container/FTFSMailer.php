<?php

namespace FTFS\MailerBundle\Container;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 *
 */
class FTFSMailer
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function send(array $message_options)
    {
        if(!array_key_exists('to', $message_options) or !is_array($message_options['to'])) {
            throw new \Exception('Destination can not be null and the form must be an array');
        }

        $message = $this->mailer->createMessage();

        if(!array_key_exists('subject', $message_options) or $message_options['subject']==null) {
            $message->setSubject('[No Reply] System Notification from FTFS Support Service');
        }else{
            $message->setSubject($message_options['subject']);
        }

        if(array_key_exists('from', $message_options)) {
            $message->setFrom($message_options['from']);
        }else{
            $message->setFrom('support@fujitsu-telecom.fr', 'FTFS Support Service Team');
        }

        $message->setTo($message_options['to']);
        if(array_key_exists('cc', $message_options)) {
            $message->setCc($message_options['cc']);
        }

        // generate messages
        if(array_key_exists('body_html', $message_options)) {
            $message->setBody($message_options['body_html'], 'text/html', 'UTF8');
        }
        if(array_key_exists('body_txt', $message_options)) {
            $message->addPart($message_options['body_txt'], 'text/plain', 'UTF8');
        }

        // attachment
        if(array_key_exists('attachments', $message_options)) {
            $attachments = $message_options['attachments'];
            foreach($attachments as $attachment) {
                $message->attach($attachment);
            }
        }

        $this->mailer->send($message);
    }

}
