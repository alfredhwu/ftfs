<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\NotificationBundle\Entity\Event
 *
 * @ORM\Table(name="ftfs_config_event_definition")
 * @ORM\Entity
 */
class Event
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
     * @var string $event_key
     *
     * @ORM\Column(name="event_key", type="string", length=255)
     */
    private $event_key;

    /**
     * @var integer $security_level
     *
     * @ORM\Column(name="security_level", type="integer")
     */
    private $security_level;

    /**
     * Get __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getEventKey();
    }

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
     * Set event_key
     *
     * @param string $event_key
     */
    public function setEventKey($event_key)
    {
        $this->event_key = $event_key;
    }

    /**
     * Get event_key
     *
     * @return string 
     */
    public function getEventKey()
    {
        return $this->event_key;
    }

    /**
     * Set security_level
     *
     * @param integer $securityLevel
     */
    public function setSecurityLevel($securityLevel)
    {
        $this->security_level = $securityLevel;
    }

    /**
     * Get security_level
     *
     * @return integer 
     */
    public function getSecurityLevel()
    {
        return $this->security_level;
    }
}