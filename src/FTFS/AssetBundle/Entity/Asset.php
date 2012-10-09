<?php

namespace FTFS\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\AssetBundle\Entity\Asset
 *
 * @ORM\Table(name="ftfs_config_asset")
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
     * @ORM\ManyToOne(targetEntity="\FTFS\ProductBundle\Entity\Product")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     */
    private $client;

    /**
     * @var datetime $installed_at
     *
     * @ORM\Column(name="installed_at", type="datetime")
     */
    private $installed_at;

    /**
     * @var string $installed_in
     *
     * @ORM\Column(name="installed_in", type="string", length=255)
     */
    private $installed_in;

    /**
     * @var text $observation
     *
     * @ORM\Column(name="observation", type="text")
     */
    private $observation;

    /**
     * Get __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName().' ("'.$this->getProduct().'" for '.$this->getClient().', in '.$this->getInstalledIn().')';
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
     * Set installed_at
     *
     * @param datetime $installedAt
     */
    public function setInstalledAt($installedAt)
    {
        $this->installed_at = $installedAt;
    }

    /**
     * Get installed_at
     *
     * @return datetime 
     */
    public function getInstalledAt()
    {
        return $this->installed_at;
    }

    /**
     * Set observation
     *
     * @param text $observation
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;
    }

    /**
     * Get observation
     *
     * @return text 
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set product
     *
     * @param FTFS\ProductBundle\Entity\Product $product
     */
    public function setProduct(\FTFS\ProductBundle\Entity\Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return FTFS\ProductBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set client
     *
     * @param FTFS\AssetBundle\Entity\Client $client
     */
    public function setClient(\FTFS\AssetBundle\Entity\Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return FTFS\AssetBundle\Entity\Client 
     */
    public function getClient()
    {
        return $this->client;
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
}
