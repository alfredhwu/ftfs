<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\NotificationBundle\Entity\EventCatchFilter
 *
 * @ORM\Table(name="ftfs_notification_event_catch_filter")
 * @ORM\Entity
 */
class EventCatchFilter
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
     *
     * @var Symfony\Component\Security\Core\User\UserInterface
     * @ORM\ManyToOne(targetEntity="FTFS\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var FTFS\NotificationBundle\Entity\Event
     * @ORM\ManyToOne(targetEntity="Event")
     */
    private $event;

    /**
     * @var FTFS\NotificationBundle\Entity\NotificationMethod
     * @ORM\ManyToOne(targetEntity="NotificationMethod")
     */
    private $method;

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
     * Set user
     *
     * @param FTFS\UserBundle\Entity\User $user
     */
    public function setUser(\FTFS\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
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
}