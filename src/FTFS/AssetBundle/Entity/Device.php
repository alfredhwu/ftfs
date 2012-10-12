<?php

namespace FTFS\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\AssetBundle\Entity\Device
 *
 * @ORM\Table(name="ftfs_config_asset_device")
 * @ORM\Entity
 */
class Device
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
     * @ORM\ManyToOne(targetEntity="\FTFS\ProductBundle\Entity\Product")
     */
    private $product;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $serial
     *
     * @ORM\Column(name="serial", type="string", length=255, nullable=true)
     */
    private $serial;

    /**
     * @ORM\ManyToOne(targetEntity="Asset", inversedBy="devices")
     */
    private $asset;

    /**
     * @var datetime $installed_at
     *
     * @ORM\Column(name="installed_at", type="datetime", nullable=true)
     */
    private $installed_at;

    /**
     * @var text $observation
     *
     * @ORM\Column(name="observation", type="text", nullable=true)
     */
    private $observation;

    /** 
     * Get __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName().' ('.$this->getProduct()->getName().')'.' * '.$this->getAsset();
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
     * Set serial
     *
     * @param string $serial
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;
    }

    /**
     * Get serial
     *
     * @return string 
     */
    public function getSerial()
    {
        return $this->serial;
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
     * Set asset
     *
     * @param FTFS\AssetBundle\Entity\Asset $asset
     */
    public function setAsset(\FTFS\AssetBundle\Entity\Asset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * Get asset
     *
     * @return FTFS\AssetBundle\Entity\Asset 
     */
    public function getAsset()
    {
        return $this->asset;
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
}
