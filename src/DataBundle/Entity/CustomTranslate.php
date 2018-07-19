<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity()
 */
class CustomTranslate{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false)
     */
    private $keyId;

    /**
     * @ORM\Column(type="string")
     */
    private $value;


    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\CustomTranslateAvailableLangs"))
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $langKey;

    /**
     * @return mixed
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @param mixed $keyId
     */
    public function setKeyId($keyId)
    {
        $this->keyId = $keyId;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
