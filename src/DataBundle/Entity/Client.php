<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Table(name="clients")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ClientRepository")
 */
class Client
{

    /**
     * @Serializer\Expose()
     * @ORM\Id
     * @ORM\Column(type="string", length=10, nullable=false, unique=true)
     */
    private $dni;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $surname1;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $surname2;

    /**
     * @ORM\Column(type="date")
     */
    private $birthdate;


    /**
     * @ORM\ManyToOne(targetEntity="DataBundle\Entity\Nationality")
     * @ORM\JoinColumn(name="nationality", referencedColumnName="id")
     */
    private $nationality;

    /**
     * @ORM\Column(type="string")
     */
    private $address;
    /**
     * @ORM\Column(type="string")
     */
    private $email;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="DataBundle\Entity\Gender")
     * @ORM\JoinColumn(name="gender", referencedColumnName="id")
     */
    private $gender;
    
    public function getDni()
    {
        return $this->dni;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSurname1()
    {
        return $this->surname1;
    }

    public function getSurname2()
    {
        return $this->surname2;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function getNationality()
    {
        return $this->nationality;
    }


    public function setDni($dni)
    {
        $this->dni = $dni;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setSurname1($surname1)
    {
        $this->surname1 = $surname1;
    }

    public function setSurname2($surname2)
    {
        $this->surname2 = $surname2;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }


    function getGender() {
        return $this->gender;
    }

    function setGender($gender) {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }


}
