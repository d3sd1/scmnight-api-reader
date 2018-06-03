<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="users_recover")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserRecoverRepository")
 */
class UserRecover
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
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    private $dateExpires;

    
    /**
     * @ORM\Column(name="code", type="string")
     */
    private $code;
    
    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getDateExpires() {
        return $this->dateExpires;
    }

    function getCode() {
        return $this->code;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setDateExpires($dateExpires) {
        $this->dateExpires = $dateExpires;
    }

    function setCode($code) {
        $this->code = $code;
    }
}
