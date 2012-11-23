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
     * @ORM\ManyToOne(targetEntity="NotificationMethod")
     */
    private $method;

    /**
     * @var text $html_message
     *
     * @ORM\Column(name="html_message", type="text", nullable=true)
     */
    private $html_message;

    /**
     * @var text $text_message
     *
     * @ORM\Column(name="text_message", type="text", nullable=true)
     */
    private $text_message;

    /**
     * @var text $mini_message
     *
     * @ORM\Column(name="mini_message", type="text", nullable=true)
     */
    private $mini_message;

    /** 
     * @var array $attachments
     *
     * @ORM\Column(name="attachments", type="array")
     */
    private $attachments;

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
     * @param FTFS\NotificationBundle\Entity\NotificationMethod $method
     */
    public function setMethod(\FTFS\NotificationBundle\Entity\NotificationMethod $method)
    {
        $this->method = $method;
    }

    /**
     * Get method
     *
     * @return FTFS\NotificationBundle\Entity\NotificationMethod 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set attachments
     *
     * @param array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * Get attachments
     *
     * @return array 
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set html_message
     *
     * @param text $htmlMessage
     */
    public function setHtmlMessage($htmlMessage)
    {
        $this->html_message = $htmlMessage;
    }

    /**
     * Get html_message
     *
     * @return text 
     */
    public function getHtmlMessage()
    {
        return $this->html_message;
    }

    /**
     * Set text_message
     *
     * @param text $textMessage
     */
    public function setTextMessage($textMessage)
    {
        $this->text_message = $textMessage;
    }

    /**
     * Get text_message
     *
     * @return text 
     */
    public function getTextMessage()
    {
        return $this->text_message;
    }

    /**
     * Set mini_message
     *
     * @param text $miniMessage
     */
    public function setMiniMessage($miniMessage)
    {
        $this->mini_message = $miniMessage;
    }

    /**
     * Get mini_message
     *
     * @return text 
     */
    public function getMiniMessage()
    {
        return $this->mini_message;
    }
}