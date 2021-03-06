<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Table(name="permissions_list")
 * @ORM\Entity()
 */
class PermissionList
{


    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $listKeyName;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getListKeyName()
    {
        return $this->listKeyName;
    }

    /**
     * @param mixed $listKeyName
     */
    public function setListKeyName($listKeyName)
    {
        $this->listKeyName = $listKeyName;
    }


}
