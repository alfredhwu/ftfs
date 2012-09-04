<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use FTFS\UserBundle\Entity\User;
use merk\NotificationBundle\Entity\NotificationEvent as BaseNotificationEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="ftfs_notification_notificationevent")
 * @ORM\HasLifecycleCallbacks
 */
class NotificationEvent extends BaseNotificationEvent  
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="FTFS\NotificationBundle\Entity\Notification", mappedBy="event")
     * @var \Doctrine\Common\Collection\Collection
     */
    protected $notifications;

    public function __construct($key, $subject, $verb, UserInterface $actor=null, DateTime $createdAt=null)
    {
        parent::__construct($key, $subject, $verb, $actor, $createdAt);
        $this->notifications = new ArrayCollection;
    }

    /**
     * set the actor for the event
     * @param null|\Symfony\Component\Security\Core\User\UserInterface $actor
     * @throws \InvalidArgumentException when the $actor is not a User object
     */
    protected function setActor(UserInterface $actor = null)
    {
        if(null !== $actor and !$actor instanceof User) 
        {
            throw new \InvalidArgumentException('Actor must be a User');
        }
        $this->actor = $actor;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }
}
