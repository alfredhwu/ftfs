<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FTFS\UserBundle\Entity\User;
use merk\NotificationBundle\Entity\Notification as BaseNotification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ftfs_notification_notification")
 */
class Notification extends BaseNotification
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FTFS\UserBundle\Entity\User")
     * @var \FTFS\UserBundle\Entity\User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="FTFS\NotificationBundle\Entity\NotificationEvent", inversedBy="notifications")
     * @var NotificationEvent
     */
    protected $event;
}
