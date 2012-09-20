<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\NotificationBundle\Entity\NotificationLog
 *
 * @ORM\Table(name="ftfs_notification_notification_log")
 * @ORM\Entity
 */
class NotificationLog
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     *
     * @var \FTFS\NotificationBundle\Entity\Event
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="FTFS\UserBundle\Entity\User")
     *
     * @var \FTFS\UserBundle\Entity\User
     */
    private $notified_to;

    /**
     * @var array $cc
     *
     * @ORM\Column(name="cc", type="array")
     */
    private $cc;

    /**
     * @var string $method
     *
     * @ORM\Column(name="method", type="string", length=10)
     */
    private $method;

    /**
     * @var text $message
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var datetime $notified_at
     *
     * @ORM\Column(name="notified_at", type="datetime", nullable=true)
     */
    private $notified_at;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set notified_at
     *
     * @param datetime $notifiedAt
     */
    public function setNotifiedAt($notifiedAt)
    {
        $this->notified_at = $notifiedAt;
    }

    /**
     * Get notified_at
     *
     * @return datetime 
     */
    public function getNotifiedAt()
    {
        return $this->notified_at;
    }

    /**
     * Set event
     *
     * @param FTFS\NotificationBundle\Entity\Event $event
     */
    public function setEvent(\FTFS\NotificationBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return FTFS\NotificationBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set notified_to
     *
     * @param FTFS\UserBundle\Entity\User $notifiedTo
     */
    public function setNotifiedTo(\FTFS\UserBundle\Entity\User $notifiedTo)
    {
        $this->notified_to = $notifiedTo;
    }

    /**
     * Get notified_to
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getNotifiedTo()
    {
        return $this->notified_to;
    }

    /**
     * Set cc
     *
     * @param array $cc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;
    }

    /**
     * Get cc
     *
     * @return array 
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set method
     *
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set message
     *
     * @param text $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return text 
     */
    public function getMessage()
    {
        return $this->message;
    }
}
