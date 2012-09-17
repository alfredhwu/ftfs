<?php

namespace FTFS\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ftfs_user")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length="255")
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\MinLength(limit="1", message="The name is too short.", groups={"Registration", "Profile"})
     * @Assert\MaxLength(limit="255", message="The name is too long.", groups={"Registration", "Profile"})
     */
    protected $surname;

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\MinLength(limit="1", message="The name is too short.", groups={"Registration", "Profile"})
     * @Assert\MaxLength(limit="255", message="The name is too long.", groups={"Registration", "Profile"})
     */
    protected $first_name;

    /**
     * @ORM\Column(type="string", length="255")
     */
    protected $company;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $address;

    /**
     * @Assert\NotBlank(message="Please enter your office phone number.", groups={"Registration", "Profile"})
     * @ORM\Column(type="string", length="255")
     */
    protected $office_phone;
    
    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     */
    protected $office_fax;

    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     */
    protected $mobile_phone;

    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     */
    protected $other_phone_1;

    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     */
    protected $other_phone_2;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function __toString()
    {
        return $this->getTitle().' '.$this->getFirstName().' '.$this->getSurname().' ('.$this->getCompany().')';
    }

    /**
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        // if invitiation, add roles
        $invitation = $this->getInvitation();
        if($invitation){
            $this->setRoles($invitation->getRoles());
        }
    }

    /**
     * @ORM\OneToOne(targetEntity="Invitation", mappedBy="user")
     * @ORM\JoinColumn(referencedColumnName="code")
     * @Assert\NotNull(message="Your invitation is wrong")
     */
    private $invitation;

    public function setInvitation(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * Set surname
     *
     * @param string surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * Get surname
     *
     * @param string surname
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set first_name
     *
     * @param string first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * Get first_name
     *
     * @param string first_name
     */
    public function getFirstName()
    {
        return $this->first_name;
    }
    /**
     * Set title
     *
     * @param string title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @param string title
     */
    public function getTitle()
    {
        return $this->title;
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
     * Set office_phone
     *
     * @param string $officePhone
     */
    public function setOfficePhone($officePhone)
    {
        $this->office_phone = $officePhone;
    }

    /**
     * Get office_phone
     *
     * @return string 
     */
    public function getOfficePhone()
    {
        return $this->office_phone;
    }

    /**
     * Set mobile_phone
     *
     * @param string $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobile_phone = $mobilePhone;
    }

    /**
     * Get mobile_phone
     *
     * @return string 
     */
    public function getMobilePhone()
    {
        return $this->mobile_phone;
    }

    /**
     * Set other_phone_1
     *
     * @param string $otherPhone1
     */
    public function setOtherPhone1($otherPhone1)
    {
        $this->other_phone_1 = $otherPhone1;
    }

    /**
     * Get other_phone_1
     *
     * @return string 
     */
    public function getOtherPhone1()
    {
        return $this->other_phone_1;
    }

    /**
     * Set other_phone_2
     *
     * @param string $otherPhone2
     */
    public function setOtherPhone2($otherPhone2)
    {
        $this->other_phone_2 = $otherPhone2;
    }

    /**
     * Get other_phone_2
     *
     * @return string 
     */
    public function getOtherPhone2()
    {
        return $this->other_phone_2;
    }

    /**
     * Set company
     *
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set office_fax
     *
     * @param string $officeFax
     */
    public function setOfficeFax($officeFax)
    {
        $this->office_fax = $officeFax;
    }

    /**
     * Get office_fax
     *
     * @return string 
     */
    public function getOfficeFax()
    {
        return $this->office_fax;
    }


    /**
     * Set address
     *
     * @param text $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return text 
     */
    public function getAddress()
    {
        return $this->address;
    }
}
