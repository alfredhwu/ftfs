<?php

namespace FTFS\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\UserBundle\Entity\Company
 *
 * @ORM\Table(name="ftfs_user_company")
 * @ORM\Entity
 */
class Company
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
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=3)
     */
    private $code;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var boolean $is_client
     *
     * @ORM\Column(name="is_client", type="boolean")
     */
    private $is_client;

    /**
     * @var boolean $is_supplier
     *
     * @ORM\Column(name="is_supplier", type="boolean")
     */
    private $is_supplier;

    /**
     * Get __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName().' ('.$this->getCode().')';
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
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set is_client
     *
     * @param boolean $isClient
     */
    public function setIsClient($isClient)
    {
        $this->is_client = $isClient;
    }

    /**
     * Get is_client
     *
     * @return boolean 
     */
    public function getIsClient()
    {
        return $this->is_client;
    }

    /**
     * Set is_supplier
     *
     * @param boolean $isSupplier
     */
    public function setIsSupplier($isSupplier)
    {
        $this->is_supplier = $isSupplier;
    }

    /**
     * Get is_supplier
     *
     * @return boolean 
     */
    public function getIsSupplier()
    {
        return $this->is_supplier;
    }
}
