<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppToken
 *
 * @ORM\Table(name="app_token")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AppTokenRepository")
 */
class AppToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", unique=true, length=1000)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Computer"))
     */
    private $computer;

    function getValue()
    {
        return $this->value;
    }

    function setValue($value)
    {
        $this->value = $value;
    }

    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdDate
     *
     * @param string $createdDate
     *
     * @return AppToken
     */
    public function setCreatedDate(\DateTime $createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @return Computer
     */
    public function setComputer(\AppBundle\Entity\Computer $computer)
    {
        $this->computer = $computer;

        return $this;
    }

    /**
     * @return Computer
     */
    public function getComputer()
    {
        return $this->computer;
    }
}

