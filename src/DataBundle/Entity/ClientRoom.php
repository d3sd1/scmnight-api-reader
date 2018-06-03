<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clients_room")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ClientRoomRepository")
 */
class ClientRoom
{
    public function __construct($client, $vip)
    {
        $this->setClient($client);
        $this->setVip($vip);
        $this->setDate(new \DateTime("now"));
    }

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\Client"))
     * @ORM\JoinColumn(name="client_dni", referencedColumnName="dni")
     */
    private $client;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $vip;
    
    function getClient()
    {
        return $this->client;
    }

    function setClient($client)
    {
        $this->client = $client;
    }

    function getDate()
    {
        return $this->date;
    }

    function getVip()
    {
        return $this->vip;
    }


    function setDate($date)
    {
        $this->date = $date;
    }

    function setVip($vip)
    {
        $this->vip = $vip;
    }

}
