<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\Service
 *
 * @ORM\Table(name="ftfs_service_service")
 * @ORM\Entity
 */
class Service
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
     * @ORM\ManyToOne(targetEntity="ServiceType")
     */
    private $type;

    /**
     * @var smallint $severity
     *
     * @ORM\Column(name="severity", type="smallint")
     */
    private $severity;

    /**
     * @var smallint $priority
     *
     * @ORM\Column(name="priority", type="smallint")
     */
    private $priority;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var datetime $last_modified_at
     *
     * @ORM\Column(name="last_modified_at", type="datetime", nullable="true")
     */
    private $last_modified_at;

    /**
     * @var datetime $request_received_at
     *
     * @ORM\Column(name="request_received_at", type="datetime")
     */
    private $request_received_at;

    /**
     * @var string $requested_by
     *
     * @ORM\Column(name="requested_by", type="string", length=255)
     */
    private $requested_by;

    /**
     * @var string $requested_via
     *
     * @ORM\Column(name="requested_via", type="string", length=255, nullable=true)
     */
    private $requested_via;

    /**
     * @var string $summary
     *
     * @ORM\Column(name="summary", type="text")
     */
    private $summary;

    /**
     * @var string $detail
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
     * @var datetime $opened_at
     *
     * @ORM\Column(name="opened_at", type="datetime")
     */
    private $opened_at;

    /**
     * @var string $assigned_to
     *
     * @ORM\Column(name="assigned_to", type="string", length=255, nullable="true")
     */
    private $assigned_to;

    /**
     * @var datetime $resolved_at
     *
     * @ORM\Column(name="resolved_at", type="datetime", nullable="true")
     */
    private $resolved_at;

    /**
     * @var datetime $closed_at
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable="true")
     */
    private $closed_at;

    /**
     * Get __toString
     *
     * @return string 
     */
    public function __toString()
    {
        return $this->getName();
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
     * Set severity
     *
     * @param smallint $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    /**
     * Get severity
     *
     * @return smallint 
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Set priority
     *
     * @param smallint $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Get priority
     *
     * @return smallint 
     */
    public function getPriority()
    {
        return $this->priority;
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
     * Set request_received_at
     *
     * @param datetime $requestReceivedAt
     */
    public function setRequestReceivedAt($requestReceivedAt)
    {
        $this->request_received_at = $requestReceivedAt;
    }

    /**
     * Get request_received_at
     *
     * @return datetime 
     */
    public function getRequestReceivedAt()
    {
        return $this->request_received_at;
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
     * Set opened_at
     *
     * @param datetime $openedAt
     */
    public function setOpenedAt($openedAt)
    {
        $this->opened_at = $openedAt;
    }

    /**
     * Get opened_at
     *
     * @return datetime 
     */
    public function getOpenedAt()
    {
        return $this->opened_at;
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
     * Set resolved_at
     *
     * @param datetime $resolvedAt
     */
    public function setResolvedAt($resolvedAt)
    {
        $this->resolved_at = $resolvedAt;
    }

    /**
     * Get resolved_at
     *
     * @return datetime 
     */
    public function getResolvedAt()
    {
        return $this->resolved_at;
    }

    /**
     * Set closed_at
     *
     * @param datetime $closedAt
     */
    public function setClosedAt($closedAt)
    {
        $this->closed_at = $closedAt;
    }

    /**
     * Get closed_at
     *
     * @return datetime 
     */
    public function getClosedAt()
    {
        return $this->closed_at;
    }

    /**
     * Set type
     *
     * @param FTFS\ServiceBundle\Entity\ServiceType $type
     */
    public function setType(\FTFS\ServiceBundle\Entity\ServiceType $type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return FTFS\ServiceBundle\Entity\ServiceType 
     */
    public function getType()
    {
        return $this->type;
    }
}