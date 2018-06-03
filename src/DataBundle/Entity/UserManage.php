<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="users_manage_logs")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserManageRepository")
 */
class UserManage
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
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\User"))
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $targetUser;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\UserManageType"))
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;
    
    
    public function __construct()
    {
        $this->date = new \DateTime("now");
    }
    function getTargetUser()
    {
        return $this->targetUser;
    }

    function setTargetUser($targetUser)
    {
        $this->targetUser = $targetUser;
    }

        function getUser()
    {
        return $this->user;
    }

    function setUser($user)
    {
        $this->user = $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }

}
