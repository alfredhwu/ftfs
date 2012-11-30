<?php

namespace FTFS\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\AssetBundle\Entity\Device
 *
 * @ORM\Table(name="ftfs_asset_device")
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
     * @ORM\Column(name="module_name", type="string", length=255, nullable=true)
     */
    private $module_name;

    /**
     * @var string $module_pn
     *
     * @ORM\Column(name="module_pn", type="string", length=255, nullable=true)
     */
    private $module_pn;

    /**
     * @var string $module_sn
     *
     * @ORM\Column(name="module_sn", type="string", length=255, nullable=true)
     */
    private $module_sn;

    /**
     * @var string $local_site
     *
     * @ORM\Column(name="local_site", type="string", length=255)
     */
    private $local_site;

    /**
     * @var string $remote_site
     *
     * @ORM\Column(name="remote_site", type="string", length=255, nullable=true)
     */
    private $remote_site;

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
     * Set module_pn
     *
     * @param string $modulePn
     */
    public function setModulePn($modulePn)
    {
        $this->module_pn = $modulePn;
    }

    /**
     * Get module_pn
     *
     * @return string 
     */
    public function getModulePn()
    {
        return $this->module_pn;
    }

    /**
     * Set module_sn
     *
     * @param string $moduleSn
     */
    public function setModuleSn($moduleSn)
    {
        $this->module_sn = $moduleSn;
    }

    /**
     * Get module_sn
     *
     * @return string 
     */
    public function getModuleSn()
    {
        return $this->module_sn;
    }

    /**
     * Set local_site
     *
     * @param string $localSite
     */
    public function setLocalSite($localSite)
    {
        $this->local_site = $localSite;
    }

    /**
     * Get local_site
     *
     * @return string 
     */
    public function getLocalSite()
    {
        return $this->local_site;
    }

    /**
     * Set remote_site
     *
     * @param string $remoteSite
     */
    public function setRemoteSite($remoteSite)
    {
        $this->remote_site = $remoteSite;
    }

    /**
     * Get remote_site
     *
     * @return string 
     */
    public function getRemoteSite()
    {
        return $this->remote_site;
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
}
