<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="computers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ComputerRepository")
 */
class Computer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="mac", type="string", unique=true)
     */
    private $mac;

    /**
     * @ORM\Column(name="description", type="string")
     */
    private $description;
    /**
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    public function __construct()
    {
        $this->createdDate = new \DateTime("now");
    }
    function getDescription()
    {
        return $this->description;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getMac()
    {
        return $this->mac;
    }

    function getActive()
    {
        return $this->active;
    }

    function getCreatedDate(): \DateTime
    {
        return $this->createdDate;
    }

    function setMac($mac)
    {
        $this->mac = $mac;
    }

    function setActive($active)
    {
        $this->active = $active;
    }

    function setCreatedDate(\DateTime $createdDate)
    {
        $this->createdDate = $createdDate;
    }
    function getRoles()
    {
        return array("");
    }
    public function __toString()
    {
        return "";
    }

}

