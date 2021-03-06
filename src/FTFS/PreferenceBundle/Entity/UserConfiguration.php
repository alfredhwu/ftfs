<?php

namespace FTFS\PreferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\PreferenceBundle\Entity\UserConfiguration
 *
 * @ORM\Table(name="ftfs_preferences_configuration_user")
 * @ORM\Entity
 */
class UserConfiguration
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
     * @var array $value
     *
     * @ORM\Column(name="value", type="array")
     */
    private $value;

    /** 
     * @ORM\ManyToOne(targetEntity="\FTFS\UserBundle\Entity\User")
     */
    private $user;

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
     * Set value
     *
     * @param array $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return array 
     */
    public function getValue()
    {
        return $this->value;
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
}
