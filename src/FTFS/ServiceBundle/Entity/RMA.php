<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\RMA
 *
 * @ORM\Table(name="ftfs_service_ticket_rma")
 * @ORM\Entity
 */
class RMA
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
     *
     * @ORM\OneToOne(targetEntity="ServiceTicket")
     */
    private $ticket;

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
}
