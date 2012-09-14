<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FTFS\ServiceBundle\Entity\ServiceTicketAttachment
 *
 * @ORM\Table(name="ftfs_service_ticket_attachment")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ServiceTicketAttachment
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
     * @var string $path
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceTicket", inversedBy="attachments")
     */
    private $ticket;

    /**
     * @ORM\ManyToOne(targetEntity="FTFS\UserBundle\Entity\User")
     * @var \FTFS\UserBundle\Entity\User
     */
    private $uploaded_by;

    /**
     * @var datetime $uploaded_at
     *
     * @ORM\Column(name="uploaded_at", type="datetime")
     */
    private $uploaded_at;

    /**
     * @Assert\File(maxSize="1000000")
     */
    private $file;

    /**
     * temp var for the renaming ...
     */
    public $filename;

    /**
     *
     * methods for the management of life circle callbacks ******************
     *
     */

    public function __construct(ServiceTicket $ticket, \FTFS\UserBundle\Entity\User $uploaded_by)
    {
        $this->setTicket($ticket);
        $this->setUploadedBy($uploaded_by);
    }

    /**
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if(null !== $this->file) {
            // generate a unique name
            //$this->setPath(uniqid().'.'.$this->getFile()->guessExtension());
            $this->setPath(uniqid('attachment_',true));
            $rename = trim($this->filename);
            if($rename === null or $rename === "") {
                //throw new \Exception('null or ""'.($rename === null));
                $rename = $this->getFile()->getClientOriginalName();
            }
            $this->setName($rename);
            $this->setUploadedAt(new \DateTime('now'));
        }
    }

    /**
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if(null === $this->getFile()) {
            return;
        }
        $this->getFile()->move($this->getUploadRootDir(), $this->getPath());
        unset($this->file);
        /*
        // move uploaded file into target dir
        $this->getFile()->move($this->getUploadRootDir(), $this->getFile()->getClientOriginalName());
        // set path to point to the updated file
        $this->setPath($this->getFile()->getClientOriginalName());

        // clean up the file
        $this->setFile(null);
         */
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if($file = $this->getAbsolutePath()){
            unlink($file);
        }
    }


    /**
     *
     * methods for the management of uploading *******************************
     *
     */
    public function getAbsolutePath()
    {
        return null === $this->getPath() ? null : $this->getUploadRootDir().'/'.$this->getPath();
    }

    public function getWebPath()
    {
        return null === $this->getPath() ? null : $this->getUploadDir().'/'.$this->getPath();
    }

    public function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/attachments';
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
     * Set path
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set file
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set ticket
     *
     * @param FTFS\ServiceBundle\Entity\ServiceTicket $ticket
     */
    public function setTicket(\FTFS\ServiceBundle\Entity\ServiceTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get ticket
     *
     * @return FTFS\ServiceBundle\Entity\ServiceTicket 
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set uploaded_at
     *
     * @param datetime $uploadedAt
     */
    public function setUploadedAt($uploadedAt)
    {
        $this->uploaded_at = $uploadedAt;
    }

    /**
     * Get uploaded_at
     *
     * @return datetime 
     */
    public function getUploadedAt()
    {
        return $this->uploaded_at;
    }

    /**
     * Set uploaded_by
     *
     * @param FTFS\UserBundle\Entity\User $uploadedBy
     */
    public function setUploadedBy(\FTFS\UserBundle\Entity\User $uploadedBy)
    {
        $this->uploaded_by = $uploadedBy;
    }

    /**
     * Get uploaded_by
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getUploadedBy()
    {
        return $this->uploaded_by;
    }
}