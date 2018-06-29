<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Table(name="clients_entrances")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ClientEntranceRepository")
 */
class ClientEntrance
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\Client"))
     * @ORM\JoinColumn(name="client_dni", referencedColumnName="dni")
     */
    private $client;

    /**
     * @var datetime $date
     * @Type("DateTime")
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vip;

    /**
     * @ORM\Column(type="boolean")
     */
    private $forceaccess;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ClientEntranceType"))
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ClientEntrancePricing"))
     * @ORM\JoinColumn(name="rate", referencedColumnName="id")
     */
    private $rate;
    
    
    public function __construct()
    {
        $this->date = new \DateTime("now");
    }
    function getClient()
    {
        return $this->client;
    }

    function setClient($client)
    {
        $this->client = $client;
    }

    
    public function getId()
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getVip()
    {
        return $this->vip;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setVip($vip)
    {
        $this->vip = $vip;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return mixed
     */
    public function getForceaccess()
    {
        return $this->forceaccess;
    }

    /**
     * @param mixed $forceaccess
     */
    public function setForceaccess($forceaccess): void
    {
        $this->forceaccess = $forceaccess;
    }


}
