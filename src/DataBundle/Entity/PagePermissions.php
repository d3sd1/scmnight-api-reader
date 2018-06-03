<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="pages_permissions")
 * @ORM\Entity()
 */
class PagePermissions
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\Permission"))
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $permission;
    
    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $pageUrl;

    function getId() {
        return $this->id;
    }

    function getPermission() {
        return $this->permission;
    }

    function getPageUrl() {
        return $this->pageUrl;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPermission($permission) {
        $this->permissions = $permission;
    }

    function setPageUrl($pageUrl) {
        $this->pageUrl = $pageUrl;
    }

}
