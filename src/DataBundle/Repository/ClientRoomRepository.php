<?php

namespace DataBundle\Repository;

use DataBundle\Entity\Client;
use Doctrine\ORM\EntityRepository;
use DataBundle\Entity\ClientEntrance;
use DataBundle\Entity\ClientRoom;

class ClientRoomRepository extends EntityRepository
{

    public function add(Client $client, $isVip)
    {
        $em = $this->getEntityManager();
        $em->persist(new ClientRoom($client, $isVip));
        $em->flush();
    }

    public function rm(ClientEntrance $pEntrance)
    {
        $this->createQueryBuilder('pr')
            ->delete()
            ->where('pr.client = :dni')
            ->setParameter("dni", $pEntrance->getClient())
            ->getQuery()
            ->getResult();
    }

}
