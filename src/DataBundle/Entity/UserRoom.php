<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="users_room")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserRoomRepository")
 */
class UserRoom
{
    public function __construct($user)
    {
        $this->setUser($user);
        $this->setDate(new \DateTime("now"));
    }

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\User"))
     */
    private $user;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;
    
    function getUser()
    {
        return $this->user;
    }

    function setUser($user)
    {
        $this->user = $user;
    }

    function getDate()
    {
        return $this->date;
    }

    function setDate($date)
    {
        $this->date = $date;
    }

}
