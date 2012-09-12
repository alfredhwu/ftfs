<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\ServiceObservation
 *
 * @ORM\Table(name="ftfs_service_observation")
 * @ORM\Entity
 */
class ServiceObservation
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
     * @ORM\ManyToOne(targetEntity="FTFS\UserBundle\Entity\User")
     * @var \FTFS\UserBundle\Entity\User
     */
    private $send_by;

    /**
     * @var datetime $send_at
     *
     * @ORM\Column(name="send_at", type="datetime")
     */
    private $send_at;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceTicket")
     * @var \FTFS\ServiceBundle\Entity\ServiceTicket
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceObservation")
     * @var \FTFS\ServiceBundle\Entity\ServiceObservation
     */
    private $attach_to;

    /**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;


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
     * Set send_at
     *
     * @param datetime $sendAt
     */
    public function setSendAt($sendAt)
    {
        $this->send_at = $sendAt;
    }

    /**
     * Get send_at
     *
     * @return datetime 
     */
    public function getSendAt()
    {
        return $this->send_at;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set send_by
     *
     * @param FTFS\UserBundle\Entity\User $sendBy
     */
    public function setSendBy(\FTFS\UserBundle\Entity\User $sendBy)
    {
        $this->send_by = $sendBy;
    }

    /**
     * Get send_by
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getSendBy()
    {
        return $this->send_by;
    }

    /**
     * Set subject
     *
     * @param FTFS\ServiceBundle\Entity\ServiceTicket $subject
     */
    public function setSubject(\FTFS\ServiceBundle\Entity\ServiceTicket $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return FTFS\ServiceBundle\Entity\ServiceTicket 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set attach_to
     *
     * @param FTFS\ServiceBundle\Entity\ServiceObservation $attachTo
     */
    public function setAttachTo(\FTFS\ServiceBundle\Entity\ServiceObservation $attachTo)
    {
        $this->attach_to = $attachTo;
    }

    /**
     * Get attach_to
     *
     * @return FTFS\ServiceBundle\Entity\ServiceObservation 
     */
    public function getAttachTo()
    {
        return $this->attach_to;
    }
}
