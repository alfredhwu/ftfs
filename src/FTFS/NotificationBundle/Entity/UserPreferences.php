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
        // add by alfredhwu
        $this->updatedAt = new \DateTime();
    }

    /**
     * method added for correction in the merk notification bundle
     */
    public function setMethod($defaultMethod)
    {
        $this->setDefaultMethod($defaultMethod);
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
     * Set filters
     *
     * @param Doctrine\Common\Collections\ArrayCollection 
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }
}
