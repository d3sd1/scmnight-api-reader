<?php

namespace RestBundle\Entity;

use DataBundle\Entity\User;
use JMS\Serializer\Annotation\Type;

class LoginData
{
    /**
     * @Type("array")
     */
    private $coords = [];

    /**
     * @Type("boolean")
     */
    private $extendedsession = false;

    /**
     * @Type("DataBundle\Entity\User")
     */
    private $user;

    function getCoords() {
        return $this->coords;
    }

    function getExtendedsession() {
        return $this->extendedsession;
    }

    function getUser(): User {
        return $this->user;
    }

    function setCoords($coords) {
        $this->coords = $coords;
    }

    function setExtendedSession($extendedsession) {
        $this->extendedsession = $extendedsession;
    }

    function setUser(User $user) {
        $this->user = $user;
    }


}

