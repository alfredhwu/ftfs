<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FTFS\UserBundle\Entity\User;
use merk\NotificationBundle\Entity\Filter as BaseFilter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ftfs_notification_userfilter")
 */
class Filter extends BaseFilter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FTFS\NotificationBundle\Entity\UserPreferences", inversedBy="filters")
     * @var UserPreferences
     */
    protected $userPreferences;
}
