<?php

namespace FTFS\PreferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\PreferenceBundle\Entity\Configuration
 *
 * @ORM\Table(name="ftfs_preferences_configuration_default")
 * @ORM\Entity
 */
class Configuration
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
}
