<?php

namespace FTFS\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FTFS\UserBundle\Entity\User;
use merk\NotificationBundle\Entity\UserPreferences as BaseUserPreferences;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ftfs_notification_userpreferences")
 */
class UserPreferences extends BaseUserPreferences
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
     * @ORM\OneToMany(targetEntity="FTFS\NotificationBundle\Entity\Filter", mappedBy="userPreferences")
     * @var \Doctrine\Common\Collection\Collection
     */
    protected $filters;

    public function __construct()
    {
        $this->filters = new ArrayCollection;
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
     * Set user
     *
     * @param FTFS\UserBundle\Entity\User $user
     */
    public function setUser(\FTFS\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return FTFS\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get filters
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }
}
