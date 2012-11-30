<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTime;

/**
 * FTFS\NotificationBundle\Entity\EventLog
 *
 * @ORM\Table(name="ftfs_notification_log_event")
 * @ORM\Entity
 */
class EventLog
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
     * @ORM\ManyToOne(targetEntity="Event")
     *
     * @var \FTFS\NotificationBundle\Entity\Event
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="FTFS\UserBundle\Entity\User")
     *
     * @var \FTFS\UserBundle\Entity\User
     */
    private $actor;

    /**
     * @var datetime $acted_at
     *
     * @ORM\Column(name="acted_at", type="datetime")
     */
    private $acted_at;

    /**
     * @var array $action
     *
     * @ORM\Column(name="action", type="array")
     */
    private $action;

    public function __construct(Event $event, UserInterface $actor = null, DateTime $acted_at = null, array $action = null)
    {
        $this->event = $event;
        $this->actor = $actor;
        if($acted_at === null) {
            $acted_at = new DateTime('now');
        }
        $this->acted_at = $acted_at;
        $this->action = $action;
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
     * Set acted_at
     *
     * @param datetime $actedAt
     */
    public function setActedAt($actedAt)
    {
        $this->acted_at = $actedAt;
    }

    /**
     * Get acted_at
     *
     * @return datetime 
     */
    public function getActedAt()
    {
        return $this->acted_at;
    }

    /**
     * Set action
     *
     * @param array $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get action
     *
     * @return array 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set event
     *
     * @param FTFS\NotificationBundle\Entity\Event $event
     */
    public function setEvent(\FTFS\NotificationBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return FTFS\NotificationBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set actor
     *
     * @param FTFS\UserBundle\Entity\User $actor
     */
    public function setActor(\FTFS\UserBundle\Entity\User $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Get actor
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getActor()
    {
        return $this->actor;
    }
}
