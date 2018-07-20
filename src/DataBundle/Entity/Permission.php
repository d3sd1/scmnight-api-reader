<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Table(name="permissions")
 * @ORM\Entity()
 */
class Permission {


    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $action;

    function getId() {
        return $this->id;
    }

    function getAction() {
        return $this->action;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setAction($action) {
        $this->action = $action;
    }



}
