<?php

namespace FTFS\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ProductBundle\Entity\Product
 *
 * @ORM\Table(name="ftfs_config_product")
 * @ORM\Entity
 */
class Product
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
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="\FTFS\UserBundle\Entity\Company")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * Get __toString
     *
     * return string
     */
    public function __toString()
    {
        return $this->getName().' -- '.$this->getSupplier();
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
     * Set supplier
     *
     * @param FTFS\UserBundle\Entity\Company $supplier
     */
    public function setSupplier(\FTFS\UserBundle\Entity\Company $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Get supplier
     *
     * @return FTFS\UserBundle\Entity\Company 
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set category
     *
     * @param FTFS\ProductBundle\Entity\Category $category
     */
    public function setCategory(\FTFS\ProductBundle\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return FTFS\ProductBundle\Entity\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }
}