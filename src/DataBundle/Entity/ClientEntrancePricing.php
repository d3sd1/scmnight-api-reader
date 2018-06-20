<?php

namespace DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;

/**
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Table(name="clients_entrance_pricing")
 * @ORM\Entity(repositoryClass="DataBundle\Repository\ClientEntranceRepository")
 */
class ClientEntrancePricing
{

    /**
     * @Serializer\Expose()
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Serializer\Expose()
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * Precio en euros con céntimos.
     * @Serializer\Expose()
     * @ORM\Column(type="float")
     */
    private $price;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

}
