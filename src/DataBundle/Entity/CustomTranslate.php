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
     * @Exclude
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $key;

    /**
     * @ORM\Column(type="string")
     */
    private $value;


    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\CustomTranslateAvailableLangs"))
     * @ORM\JoinColumn(onDelete="CASCADE")
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
