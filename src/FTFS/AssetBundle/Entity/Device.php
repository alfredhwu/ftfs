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
     * @var string $module_name
     *
     * @ORM\Column(name="module_name", type="string", length=255)
     */
    private $module_name;

    /**
     * @var string $serial_pn
     *
     * @ORM\Column(name="serial_pn", type="string", length=255)
     */
    private $serial_pn;

    /**
     * @var string $serial_sn
     *
     * @ORM\Column(name="serial_sn", type="string", length=255)
     */
    private $serial_sn;

    /**
     * @var string $location
     *
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;

    /**
     * @var text $specification
     *
     * @ORM\Column(name="specification", type="text", nullable=true)
     */
    private $specification;

    /** 
     * Get __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getModuleName();
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
     * Set module_name
     *
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->module_name = $moduleName;
    }

    /**
     * Get module_name
     *
     * @return string 
     */
    public function getModuleName()
    {
        return $this->module_name;
    }

    /**
     * Set serial_pn
     *
     * @param string $serialPn
     */
    public function setSerialPn($serialPn)
    {
        $this->serial_pn = $serialPn;
    }

    /**
     * Get serial_pn
     *
     * @return string 
     */
    public function getSerialPn()
    {
        return $this->serial_pn;
    }

    /**
     * Set serial_sn
     *
     * @param string $serialSn
     */
    public function setSerialSn($serialSn)
    {
        $this->serial_sn = $serialSn;
    }

    /**
     * Get serial_sn
     *
     * @return string 
     */
    public function getSerialSn()
    {
        return $this->serial_sn;
    }

    /**
     * Set specification
     *
     * @param text $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * Get specification
     *
     * @return text 
     */
    public function getSpecification()
    {
        return $this->specification;
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
     * Set location
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }
}
