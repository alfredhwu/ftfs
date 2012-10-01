<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

/**
 * An agent that will send notifications through sms.
 *
 */
class SMSSenderAgent implements SenderAgentInterface
{
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notificationlog) {
        $destinaire = $notification->getNotifiedTo();
        if(!$destinaire) {
            throw new \Exception('Destinaire must be set for any notification');
        }
        $to = $destinaire->getMobileNumber();
        $body_txt = $notification->getMessage();
        $url = sprintf('http://run.orangeapi.com/sms/sendSMS.xml?id=478648c3ced&from=38100&to=%s&content=%s&ack="true"&tag="ticket"', $to, $body_txt);
        throw new \Exception($url);
        /*
         * 
        $this->ftfs_mailer->send(array(
            'subject' => $subject,
            //'from' => 'blabla',
            'to' => array($notification->getNotifiedTo()->getEmail() => $notification->getNotifiedTo()),
            'cc' => $notification->getCc(),
            'body_html' => $notification->getMessage(),
        ));
         * http://run.orangeapi.com/sms/sendSMS.xml?id=478648c3ced&from=38100&to=33646196807&content="coucou"&ack="true"&tag="ticket"
         */
        if(true) {
            $notification->setNotifiedAt(new \DateTime('now'));
        }
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
