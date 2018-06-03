<?php

namespace DataBundle\Repository;

use Doctrine\ORM\EntityRepository;
use DataBundle\Entity\UserEntrance;
use DataBundle\Entity\UserRoom;

class UserRoomRepository extends EntityRepository
{

    private function transform(UserEntrance $object)
    {
        return new UserRoom($object->getUser());
    }

    public function add(UserEntrance $pEntrance)
    {
        $entrance = $this->transform($pEntrance);
        $em = $this->getEntityManager();
        $em->persist($entrance);
        $em->flush();
    }

    public function rm(UserEntrance $pEntrance)
    {
        $this->createQueryBuilder('pr')
            ->delete()
            ->where('pr.user = :dni')
            ->setParameter("dni", $pEntrance->getUser())
            ->getQuery()
            ->getResult();
    }

}
