<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;

/**
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Table(name="clients_entrance_type")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ClientEntranceRepository")
 */
class ClientEntranceType
{

    /**
     * @Serializer\Expose()
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @Serializer\Expose()
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $this->name = $name;
    }

}
