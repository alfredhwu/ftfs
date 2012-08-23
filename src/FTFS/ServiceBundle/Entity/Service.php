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
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string $severity
     *
     * @ORM\Column(name="severity", type="string", length=255)
     */
    private $severity;

    /**
     * @var string $priority
     *
     * @ORM\Column(name="priority", type="string", length=255)
     */
    private $priority;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var datetime $request_received_at
     *
     * @ORM\Column(name="request_received_at", type="datetime")
     */
    private $request_received_at;

    /**
     * @var datetime $opened_at
     *
     * @ORM\Column(name="opened_at", type="datetime")
     */
    private $opened_at;

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
     * @var datetime $last_modified_at
     *
     * @ORM\Column(name="last_modified_at", type="datetime", nullable="true")
     */
    private $last_modified_at;

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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set severity
     *
     * @param string $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    /**
     * Get severity
     *
     * @return string 
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Set priority
     *
     * @param string $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Get priority
     *
     * @return string 
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
}
