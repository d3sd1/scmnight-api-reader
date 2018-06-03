<?php

namespace DataBundle\Repository;

use Doctrine\ORM\EntityRepository;
use DataBundle\Entity\ClientEntrance;
use DataBundle\Entity\ClientRoom;

class ClientRoomRepository extends EntityRepository
{

    private function transform(ClientEntrance $object)
    {
        return new ClientRoom($object->getClient(), $object->getVip());
    }

    public function add(ClientEntrance $pEntrance)
    {
        $entrance = $this->transform($pEntrance);
        $em = $this->getEntityManager();
        $em->persist($entrance);
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
