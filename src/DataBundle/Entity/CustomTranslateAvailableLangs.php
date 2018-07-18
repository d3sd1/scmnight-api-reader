<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity()
 */
class CustomTranslateAvailableLangs
{

    /**
     * @ORM\Id
     * @Exclude
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $langKey;

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
    public function getLangKey()
    {
        return $this->langKey;
    }

    /**
     * @param mixed $langKey
     */
    public function setLangKey($langKey)
    {
        $this->langKey = $langKey;
    }
}