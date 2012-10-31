<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\ServiceTicketMeta
 *
 * @ORM\Table(name="ftfs_service_ticket_meta")
 * @ORM\Entity
 */
class ServiceTicketMeta
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
     *
     * @ORM\ManyToOne(targetEntity="ServiceTicket")
     */
    private $ticket;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;


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
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
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
