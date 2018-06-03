<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Table(name="clients")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ClientRepository")
 */
class Client
{

    /**
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
     *
     * @ORM\Column(type="date")
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=100)
     * @Exclude
     */
    private $serialNumber;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $nationality;

    /**
     * @ORM\Column(type="text")
     * @Exclude
     */
    private $biometric;
    
    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $conflictive;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ClientGender"))
     * @ORM\JoinColumn(onDelete="SET NULL")
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

    public function getBirthDate()
    {
        return $this->birthDate;
    }

    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    public function getNationality()
    {
        return $this->nationality;
    }

    public function getBiometric()
    {
        return $this->biometric;
    }

    public function getConflictive()
    {
        return $this->conflictive;
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

    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;
    }

    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    public function setBiometric($biometric)
    {
        $this->biometric = $biometric;
    }

    public function setConflictive($conflictive)
    {
        $this->conflictive = $conflictive;
    }

    function getGender() {
        return $this->gender;
    }

    function setGender($gender) {
        $this->gender = $gender;
    }

}
