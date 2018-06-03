<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="users_panel_logins")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserManageRepository")
 */
class UserLogin
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\User"))
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;
    
    /**
     * @ORM\Column(type="float")
     */
    private $lat;
    
    /**
     * @ORM\Column(type="float")
     */
    private $lng;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    
    public function __construct()
    {
        $this->date = new \DateTime("now");
    }
    function getId()
    {
        return $this->id;
    }

    function getUser()
    {
        return $this->user;
    }

    function getLat()
    {
        return $this->lat;
    }

    function getLng()
    {
        return $this->lng;
    }

    function getDate()
    {
        return $this->date;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUser($user)
    {
        $this->user = $user;
    }

    function setLat($lat)
    {
        $this->lat = $lat;
    }

    function setLng($lng)
    {
        $this->lng = $lng;
    }

    function setDate($date)
    {
        $this->date = $date;
    }
}
