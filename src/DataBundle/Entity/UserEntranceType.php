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
    private $name;
    
    function getId() {
        return $this->id;
    }

    function getName()
    {
        return $this->name;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name)
    {
        $this->name = $name;
    }

}
