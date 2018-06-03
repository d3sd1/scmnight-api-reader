<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="config_manage_logs")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\UserManageRepository")
 */
class ConfigManage {

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\User"))
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ConfigType"))
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $targetConfig;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct() {
        $this->date = new \DateTime("now");
    }

    function getTargetConfig() {
        return $this->targetConfig;
    }

    function setTargetConfig($targetConfig) {
        $this->targetConfig = $targetConfig;
    }

    function getUser() {
        return $this->user;
    }

    function setUser($user) {
        $this->user = $user;
    }

    public function getId() {
        return $this->id;
    }

    public function getDate() {
        return $this->date;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDate($date) {
        $this->date = $date;
    }

}
