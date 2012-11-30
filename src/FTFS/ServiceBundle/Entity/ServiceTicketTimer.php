<?php

namespace FTFS\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\ServiceBundle\Entity\ServiceTicketTimer
 *
 * @ORM\Table(name="ftfs_service_ticket_timer")
 * @ORM\Entity
 */
class ServiceTicketTimer
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
     * @var datetime $quand
     *
     * @ORM\Column(name="quand", type="datetime")
     */
    private $quand;

    /**
     * @var string $qui
     *
     * @ORM\Column(name="qui", type="string", length=255)
     */
    private $qui;

    /**
     * @var text $quoi
     *
     * @ORM\Column(name="quoi", type="text")
     */
    private $quoi;

    /**
     * @var text $pourquoi
     *
     * @ORM\Column(name="pourquoi", type="text")
     */
    private $pourquoi;

    /**
     * @var string $flag
     *
     * @ORM\Column(name="flag", type="string", length=255)
     */
    private $flag;

    /**
     * @var string $alias
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable = true)
     */
    private $alias;

    /**
     * @var string $ticket
     *
     * @ORM\Column(name="ticket", type="string", length=255)
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
     * Set quand
     *
     * @param datetime $quand
     */
    public function setQuand($quand)
    {
        $this->quand = $quand;
    }

    /**
     * Get quand
     *
     * @return datetime 
     */
    public function getQuand()
    {
        return $this->quand;
    }

    /**
     * Set qui
     *
     * @param string $qui
     */
    public function setQui($qui)
    {
        $this->qui = $qui;
    }

    /**
     * Get qui
     *
     * @return string 
     */
    public function getQui()
    {
        return $this->qui;
    }

    /**
     * Set quoi
     *
     * @param text $quoi
     */
    public function setQuoi($quoi)
    {
        $this->quoi = $quoi;
    }

    /**
     * Get quoi
     *
     * @return text 
     */
    public function getQuoi()
    {
        return $this->quoi;
    }

    /**
     * Set pourquoi
     *
     * @param text $pourquoi
     */
    public function setPourquoi($pourquoi)
    {
        $this->pourquoi = $pourquoi;
    }

    /**
     * Get pourquoi
     *
     * @return text 
     */
    public function getPourquoi()
    {
        return $this->pourquoi;
    }

    /**
     * Set flag
     *
     * @param string $flag
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

    /**
     * Get flag
     *
     * @return string 
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * Set alias
     *
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set ticket
     *
     * @param string $ticket
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get ticket
     *
     * @return string 
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}