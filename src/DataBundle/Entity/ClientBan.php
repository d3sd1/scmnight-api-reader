<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;

/**
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Table(name="clients_bans")
 * @ORM\Entity()
 */
class ClientBan
{

    /**
     * @ORM\Id
     * @Serializer\Expose()
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\ClientBanType"))
     * @ORM\JoinColumn(name="ban", referencedColumnName="id")
     */
    private $ban;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\DataBundle\Entity\Client"))
     * @ORM\JoinColumn(name="client", referencedColumnName="dni")
     */
    private $client;

    /**
     * @return mixed
     */
    public function getBan()
    {
        return $this->ban;
    }

    /**
     * @param mixed $ban
     */
    public function setBan($ban): void
    {
        $this->ban = $ban;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client): void
    {
        $this->client = $client;
    }
}
