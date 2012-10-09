<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\ServiceTicket
 *
 * @ORM\Table(name="ftfs_service_ticket")
 * @ORM\Entity
 */
class ServiceTicket
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
     * @ORM\ManyToOne(targetEntity="Service")
     */
    private $service;

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
     * @var datetime $requested_at
     *
     * @ORM\Column(name="requested_at", type="datetime", nullable=true)
     */
    private $requested_at;

    /**
     *
     * @ORM\ManyToOne(targetEntity="\FTFS\UserBundle\Entity\User")
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
     * invisible for the client ********************************************************************
     *
     *
     * @ORM\ManyToOne(targetEntity="\FTFS\UserBundle\Entity\User")
     */
    private $assigned_to;

    /**
     * @var string $asset
     *
     * @ORM\ManyToOne(targetEntity="\FTFS\AssetBundle\Entity\Asset")
     */
    private $asset;

    /**
     * @var datetime $last_modified_at
     *
     * @ORM\Column(name="last_modified_at", type="datetime")
     */
    private $last_modified_at;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @var datetime $opened_at
     *
     * @ORM\Column(name="opened_at", type="datetime", nullable="true")
     */
    private $opened_at;

    /**
     * @var datetime $closed_at
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable="true")
     */
    private $closed_at;

    /**
     *
     * @var array $share_list
     * @ORM\Column(name="share_list", type="array")
     */
    private $share_list;

    /**
     * @ORM\OneToMany(targetEntity="ServiceTicketAttachment", mappedBy="ticket", cascade={"remove"})
     */
    private $attachments;

    /**
     * @ORM\OneToMany(targetEntity="ServiceTicketObservation", mappedBy="ticket", cascade={"remove"})
     */
    private $observations;

    /**
     * Get __toString
     *
     * @return string 
     */
    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->share_list = array();
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
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
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
     * Set service
     *
     * @param FTFS\ServiceBundle\Entity\Service $service
     */
    public function setService(\FTFS\ServiceBundle\Entity\Service $service)
    {
        $this->service = $service;
    }

    /**
     * Get service
     *
     * @return FTFS\ServiceBundle\Entity\Service 
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set requested_by
     *
     * @param FTFS\UserBundle\Entity\User $requestedBy
     */
    public function setRequestedBy(\FTFS\UserBundle\Entity\User $requestedBy)
    {
        $this->requested_by = $requestedBy;
    }

    /**
     * Get requested_by
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getRequestedBy()
    {
        return $this->requested_by;
    }

    /**
     * Set assigned_to
     *
     * @param FTFS\UserBundle\Entity\User $assignedTo
     */
    public function setAssignedTo(\FTFS\UserBundle\Entity\User $assignedTo)
    {
        $this->assigned_to = $assignedTo;
    }

    /**
     * Get assigned_to
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getAssignedTo()
    {
        return $this->assigned_to;
    }

    /**
     * Add attachments
     *
     * @param FTFS\ServiceBundle\Entity\ServiceTicketAttachment $attachments
     */
    public function addServiceTicketAttachment(\FTFS\ServiceBundle\Entity\ServiceTicketAttachment $attachments)
    {
        $this->attachments[] = $attachments;
    }

    /**
     * Get attachments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Add observations
     *
     * @param FTFS\ServiceBundle\Entity\ServiceTicketObservation $observations
     */
    public function addServiceTicketObservation(\FTFS\ServiceBundle\Entity\ServiceTicketObservation $observations)
    {
        $this->observations[] = $observations;
    }

    /**
     * Get observations
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * add share_list
     *
     * @param array $sharelist
     */
    public function addShareList($sharelist)
    {
        $list = array_unique(array_merge($this->getShareList(), $sharelist));
        asort($list);
        $this->setShareList($list);
//        throw new \Exception(print_r($this->share_list));
    }

    /**
     * delete share_list
     *
     * @param string $email
     */
    public function removeShareList($email)
    {
        $list = $this->getShareList();
        if(array_key_exists($email, $list)) {
            unset($list[$email]);
            $this->setShareList($list);
        }
//        throw new \Exception(print_r($this->share_list));
    }
    /**
     * Set share_list
     *
     * @param array $shareList
     */
    public function setShareList($shareList)
    {
        $this->share_list = $shareList;
    }

    /**
     * Get share_list
     *
     * @return array 
     */
    public function getShareList()
    {
        return $this->share_list;
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
}