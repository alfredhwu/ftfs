<?php

namespace FTFS\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FTFS\UserBundle\Entity\Invitation
 *
 * @ORM\Table(name="ftfs_user_invitation")
 * @ORM\Entity
 */
class Invitation
{
    const ROLE_DEFAULT = 'ROLE_USER';
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=6)
     * @ORM\Id
     */
    private $code;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var array
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @var boolean $sent
     *
     * @ORM\Column(name="sent", type="boolean")
     */
    private $sent = false;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="invitation", cascade={"persist", "merge"})
     */
    private $user;

    public function __construct()
    {
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
        $this->roles = array();
    }

    /**
     *
     */
    public function isSent()
    {
        return $this->sent;
    }

    public function send()
    {
        $this->sent = true;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set sent
     *
     * @param boolean $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * Get sent
     *
     * @return boolean 
     */
    public function getSent()
    {
        return $this->sent;
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
     * add role
     * @param string $role
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if($role === static::ROLE_DEFAULT) {
            return;
        }

        if(!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }
    /**
     * Set roles
     *
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = array();

        foreach($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }
    /**
     * Get roles
     *
     * @return array roles 
     */
    public function getRoles()
    {
        $roles = $this->roles;

        /*
        foreach($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }
         */

        $roles[] = static::ROLE_DEFAULT;
        return array_unique($roles);
    }
}
