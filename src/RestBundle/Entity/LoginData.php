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
    private $extendedSession = false;

    /**
     * @Type("DataBundle\Entity\User")
     */
    private $user;

    /**
     * @return mixed
     */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
     * @param mixed $coords
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;
    }

    /**
     * @return mixed
     */
    public function getExtendedSession()
    {
        return $this->extendedSession;
    }

    /**
     * @param mixed $extendedSession
     */
    public function setExtendedSession($extendedSession)
    {
        $this->extendedSession = $extendedSession;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}

