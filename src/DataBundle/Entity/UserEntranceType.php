<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Table(name="users_entrance_type")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserEntranceRepository")
 */
class UserEntranceType
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $type;
    
    function getId() {
        return $this->id;
    }

    function getType() {
        return $this->type;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setType($type) {
        $this->type = $type;
    }

}
