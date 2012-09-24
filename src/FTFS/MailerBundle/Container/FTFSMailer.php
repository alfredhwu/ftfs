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
        $message->setSubject('[No Reply] Default system notification from FTFS Support Service');
        $message->setTo($message_options['to']);
        if(array_key_exists('cc', $message_options)) {
            $message->setCc($message_options['cc']);
        }
        if(array_key_exists('body_html', $message_options)) {
            $titles = array_values($message_options['to']);
            $message->addPart($this->templating->render('FTFSMailerBundle:Message:notification.html.twig',array(
                'title' => $titles[0],
                'body' => $message_options['body_html'])), 'text/html', 'UTF8');
        }elseif(array_key_exists('body_txt', $message_options)) {
            $titles = array_values($message_options['to']);
            $message->addPart($this->templating->render('FTFSMailerBundle:Message:notification.txt.twig',array(
                'title' => $titles[0],
                'body' => $message_options['body_txt'])), 'text/plain', 'UTF8');
        }else{
            throw new \Exception('Body can not be null');
        }
        if(array_key_exists('from', $message_options)) {
            $message->setFrom($message_options['from']);
        }else{
            $message->setFrom('support@fujitsu-telecom.fr', 'FTFS Support Service Team');
        }

        $this->mailer->send($message);
        //throw new \Exception('coucou, mailer called in ftfs_mailer');
    }

}
