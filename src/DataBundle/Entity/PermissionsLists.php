<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Table(name="permissions_lists")
 * @ORM\Entity()
 */
class PermissionsLists
{


    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DataBundle\Entity\Permission")
     * @ORM\JoinColumn(name="gender", referencedColumnName="id")
     */
    private $idPermission;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DataBundle\Entity\PermissionList")
     * @ORM\JoinColumn(name="gender", referencedColumnName="id")
     */
    private $idList;

    /**
     * @return mixed
     */
    public function getIdPermission()
    {
        return $this->idPermission;
    }

    /**
     * @param mixed $idPermission
     */
    public function setIdPermission($idPermission): void
    {
        $this->idPermission = $idPermission;
    }

    /**
     * @return mixed
     */
    public function getIdList()
    {
        return $this->idList;
    }

    /**
     * @param mixed $idList
     */
    public function setIdList($idList): void
    {
        $this->idList = $idList;
    }
}
