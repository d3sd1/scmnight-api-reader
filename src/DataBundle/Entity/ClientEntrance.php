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
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ClientEntranceType"))
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;
    
    
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

}
