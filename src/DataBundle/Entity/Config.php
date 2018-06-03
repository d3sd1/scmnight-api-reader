<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Table(name="config")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ConfigRepository")
 */
class Config {

    public function __construct(PageRepository $repository) {
        $this->repository = $repository;
    }

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
    private $config;

    /**
     * @ORM\Column(type="string")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ConfigType"))
     * @ORM\JoinColumn(name="dataType", referencedColumnName="id")
     */
    private $dataType;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set config
     *
     * @param string $config
     *
     * @return Config
     */
    public function setConfig($config) {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return string
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Config
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    function getDataType() {
        return $this->dataType;
    }

    function setDataType($dataType) {
        $this->dataType = $dataType;
    }

}
