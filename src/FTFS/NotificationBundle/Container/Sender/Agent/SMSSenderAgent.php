<?php

namespace FTFS\NotificationBundle\Container\Sender\Agent;

/**
 * An agent that will send notifications through sms.
 *
 */
class SMSSenderAgent implements SenderAgentInterface
{
    public function send(\FTFS\NotificationBundle\Entity\NotificationLog $notificationlog) {
        $destinaire = $notificationlog->getNotifiedTo();
        if(!$destinaire) {
            throw new \Exception('Destinaire must be set for any notification');
        }
        if($destinaire->getMobilePhone()) {
            $to = '33'.substr($destinaire->getMobilePhone(),-9);
            $body_txt = urlencode($notificationlog->getMessage());
            $url = 'http://run.orangeapi.com/sms/sendSMS.xml?id=478648c3ced&from=38100&to='.$to.'&content='.$body_txt.'&ack=true&tag=ticket';
            $result = file_get_contents($url);
            if($result) {
                $notificationlog->setNotifiedAt(new \DateTime('now'));
            }
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
