<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 */
class ConflictReasonManage
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
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ClientBanType"))
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $conflictReason;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ConflictReasonManageType"))
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;
    
    
    public function __construct()
    {
        $this->date = new \DateTime("now");
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

    /**
     * @return mixed
     */
    public function getConflictReason()
    {
        return $this->conflictReason;
    }

    /**
     * @param mixed $conflictReason
     */
    public function setConflictReason($conflictReason)
    {
        $this->conflictReason = $conflictReason;
    }

}
