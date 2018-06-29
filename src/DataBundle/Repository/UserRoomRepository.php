<?php

namespace DataBundle\Repository;

use DataBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use DataBundle\Entity\UserEntrance;
use DataBundle\Entity\UserRoom;

class UserRoomRepository extends EntityRepository
{

    public function add(UserEntrance $pEntrance)
    {
        $em = $this->getEntityManager();
        $userInRoom = new UserRoom($pEntrance->getUser());
        $em->persist($userInRoom);
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
