<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\NotificationBundle\Entity\EventCatchFilterDefault
 *
 * @ORM\Table(name="ftfs_config_event_catch_filter_default")
 * @ORM\Entity
 */
class EventCatchFilterDefault
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
