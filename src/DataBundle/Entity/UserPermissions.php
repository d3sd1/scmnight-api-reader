<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="users_permissions")
 * @ORM\Entity()
 */
class UserPermissions
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
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\Permission"))
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $permission;

    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getPermission() {
        return $this->permission;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setPermission($permission) {
        $this->permission = $permission;
    }
}
