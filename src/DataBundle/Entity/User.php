<?php

namespace DataBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @Exclude
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @Exclude(if="context.getDirection() === 1")
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     */
    private $lastname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="dni", type="string", length=25, nullable=false, unique=true)
     */
    private $dni;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=100, nullable=false)
     */
    private $address;
    
    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=40, nullable=false)
     */
    private $telephone;

    /**
     * @var string
     * @Exclude
     */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(name="langcode", type="string", length=4, nullable=true)
     */
    private $langCode;

    //TODO: continuar permisos aqui
    /*
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\Permissions"))
     * @ORM\JoinColumn(onDelete="SET NULL")

    private $permissions;
    */
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
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
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set plainPassword
     *
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get plainPassword
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * Get salt
     *
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Erase credentials
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    function getDni()
    {
        return $this->dni;
    }

    function getAddress()
    {
        return $this->address;
    }

    function getTelephone()
    {
        return $this->telephone;
    }

    function setDni($dni)
    {
        $this->dni = $dni;
    }

    function setAddress($address)
    {
        $this->address = $address;
    }

    function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    function getLangCode()
    {
        return $this->langCode;
    }

    function setLangCode($langCode)
    {
        $this->langCode = $langCode;
    }

}

