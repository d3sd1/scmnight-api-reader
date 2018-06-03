<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="users_genders")
 * @ORM\Entity()
 */
class ClientGender
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=100, unique=true)
     */
    private $gender;

    function getId() {
        return $this->id;
    }

    function getGender() {
        return $this->gender;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setGender($gender) {
        $this->gender = $gender;
    }
}
