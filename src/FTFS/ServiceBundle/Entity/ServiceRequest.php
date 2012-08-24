<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\ServiceRequest
 *
 * @ORM\Table(name="ftfs_service_request")
 * @ORM\Entity
 */
class ServiceRequest
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
     * @var string $requested_by
     *
     * @ORM\Column(name="requested_by", type="string", length=255)
     */
    private $requested_by;

    /**
     * @var datetime $requested_at
     *
     * @ORM\Column(name="requested_at", type="datetime")
     */
    private $requested_at;

    /**
     * @var string $assigned_to
     *
     * @ORM\Column(name="assigned_to", type="string", length=255, nullable="true")
     */
    private $assigned_to;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var datetime $last_modified_at
     *
     * @ORM\Column(name="last_modified_at", type="datetime", nullable=true)
     */
    private $last_modified_at;

    /**
     * @var text $summary
     *
     * @ORM\Column(name="summary", type="text")
     */
    private $summary;

    /**
     * @var text $detail
     *
     * @ORM\Column(name="detail", type="text", nullable=true)
     */
    private $detail;

    /**
     * @var string $asset_name
     *
     * @ORM\Column(name="asset_name", type="string", length=255, nullable=true)
     */
    private $asset_name;

    /**
     * @ORM\ManyToOne(targetEntity="Service")
     */
    private $service_deployed;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceType")
     */
    private $service_requested;

    /**
     * @var string $requested_via
     *
     * @ORM\Column(name="requested_via", type="string", length=255, nullable=true)
     */
    private $requested_via;


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
     * Set requested_by
     *
     * @param string $requestedBy
     */
    public function setRequestedBy($requestedBy)
    {
        $this->requested_by = $requestedBy;
    }

    /**
     * Get requested_by
     *
     * @return string 
     */
    public function getRequestedBy()
    {
        return $this->requested_by;
    }

    /**
     * Set requested_at
     *
     * @param datetime $requestedAt
     */
    public function setRequestedAt($requestedAt)
    {
        $this->requested_at = $requestedAt;
    }

    /**
     * Get requested_at
     *
     * @return datetime 
     */
    public function getRequestedAt()
    {
        return $this->requested_at;
    }

    /**
     * Set assigned_to
     *
     * @param string $assignedTo
     */
    public function setAssignedTo($assignedTo)
    {
        $this->assigned_to = $assignedTo;
    }

    /**
     * Get assigned_to
     *
     * @return string 
     */
    public function getAssignedTo()
    {
        return $this->assigned_to;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set last_modified_at
     *
     * @param datetime $lastModifiedAt
     */
    public function setLastModifiedAt($lastModifiedAt)
    {
        $this->last_modified_at = $lastModifiedAt;
    }

    /**
     * Get last_modified_at
     *
     * @return datetime 
     */
    public function getLastModifiedAt()
    {
        return $this->last_modified_at;
    }

    /**
     * Set summary
     *
     * @param text $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * Get summary
     *
     * @return text 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set detail
     *
     * @param text $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * Get detail
     *
     * @return text 
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set asset_name
     *
     * @param string $assetName
     */
    public function setAssetName($assetName)
    {
        $this->asset_name = $assetName;
    }

    /**
     * Get asset_name
     *
     * @return string 
     */
    public function getAssetName()
    {
        return $this->asset_name;
    }

    /**
     * Set requested_via
     *
     * @param string $requestedVia
     */
    public function setRequestedVia($requestedVia)
    {
        $this->requested_via = $requestedVia;
    }

    /**
     * Get requested_via
     *
     * @return string 
     */
    public function getRequestedVia()
    {
        return $this->requested_via;
    }

    /**
     * Set service_deployed
     *
     * @param FTFS\ServiceBundle\Entity\Service $serviceDeployed
     */
    public function setServiceDeployed(\FTFS\ServiceBundle\Entity\Service $serviceDeployed)
    {
        $this->service_deployed = $serviceDeployed;
    }

    /**
     * Get service_deployed
     *
     * @return FTFS\ServiceBundle\Entity\Service 
     */
    public function getServiceDeployed()
    {
        return $this->service_deployed;
    }

    /**
     * Set service_requested
     *
     * @param FTFS\ServiceBundle\Entity\ServiceType $serviceRequested
     */
    public function setServiceRequested(\FTFS\ServiceBundle\Entity\ServiceType $serviceRequested)
    {
        $this->service_requested = $serviceRequested;
    }

    /**
     * Get service_requested
     *
     * @return FTFS\ServiceBundle\Entity\ServiceType 
     */
    public function getServiceRequested()
    {
        return $this->service_requested;
    }
}