<?php

namespace FTFS\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\AssetBundle\Entity\Asset
 *
 * @ORM\Table(name="ftfs_asset")
 * @ORM\Entity
 */
class Asset
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
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="\FTFS\UserBundle\Entity\Company")
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity="Device", mappedBy="asset", cascade={"remove"})
     */
    private $devices;

    /**
     * @var string $installed_in
     *
     * @ORM\Column(name="installed_in", type="string", length=255)
     */
    private $installed_in;

    /**
     * Get __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName().' - '.$this->getClient();
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
     * Set installed_in
     *
     * @param string $installedIn
     */
    public function setInstalledIn($installedIn)
    {
        $this->installed_in = $installedIn;
    }

    /**
     * Get installed_in
     *
     * @return string 
     */
    public function getInstalledIn()
    {
        return $this->installed_in;
    }

    /**
     * Set category
     *
     * @param FTFS\AssetBundle\Entity\Category $category
     */
    public function setCategory(\FTFS\AssetBundle\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return FTFS\AssetBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }
    public function __construct()
    {
        $this->devices = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add devices
     *
     * @param FTFS\AssetBundle\Entity\Device $devices
     */
    public function addDevice(\FTFS\AssetBundle\Entity\Device $devices)
    {
        $this->devices[] = $devices;
    }

    /**
     * Get devices
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * Set client
     *
     * @param FTFS\UserBundle\Entity\Company $client
     */
    public function setClient(\FTFS\UserBundle\Entity\Company $client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return FTFS\UserBundle\Entity\Company 
     */
    public function getClient()
    {
        return $this->client;
    }
}
