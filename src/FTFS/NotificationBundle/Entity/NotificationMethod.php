<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\NotificationBundle\Entity\NotificationMethod
 *
 * @ORM\Table(name="ftfs_notification_config_method")
 * @ORM\Entity
 */
class NotificationMethod
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean $is_enabled_client
     *
     * @ORM\Column(name="is_enabled_client", type="boolean")
     */
    private $is_enabled_client;

    /**
     * @var boolean $is_enabled_agent
     *
     * @ORM\Column(name="is_enabled_agent", type="boolean")
     */
    private $is_enabled_agent;

    public function __toString()
    {
        return $this->getName();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set is_enabled_client
     *
     * @param boolean $isEnabledClient
     */
    public function setIsEnabledClient($isEnabledClient)
    {
        $this->is_enabled_client = $isEnabledClient;
    }

    /**
     * Get is_enabled_client
     *
     * @return boolean 
     */
    public function getIsEnabledClient()
    {
        return $this->is_enabled_client;
    }

    /**
     * Set is_enabled_agent
     *
     * @param boolean $isEnabledAgent
     */
    public function setIsEnabledAgent($isEnabledAgent)
    {
        $this->is_enabled_agent = $isEnabledAgent;
    }

    /**
     * Get is_enabled_agent
     *
     * @return boolean 
     */
    public function getIsEnabledAgent()
    {
        return $this->is_enabled_agent;
    }
}