<?php
namespace DataBundle\Repository;

use DataBundle\Entity\UserEntrance;
use Doctrine\ORM\EntityRepository;

class UserEntranceRepository extends EntityRepository
{
    public function getEntranceType($user)
    {
        $entrance = $this->createQueryBuilder('pe')
            ->where('pe.user=:user')
            ->setParameter('user', $user)
            ->orderBy('pe.date', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $entranceType = "JOIN";

        if ($entrance != null) {
            $entranceNamedType = $this->getEntityManager()->getRepository('DataBundle:UserEntranceType')->findOneBy(["id" => $entrance->getType()->getId()])->getName();
            switch ($entranceNamedType) {
                case 'JOIN':
                    $entranceType = "LEAVE";
                    break;
                case 'LEAVE':
                    $entranceType = "JOIN";
                    break;
            }
        }
        $entrance = new UserEntrance();
        $entrance->setType($this->getEntityManager()->getRepository('DataBundle:UserEntranceType')->findOneBy(["name" => $entranceType]));
        return $entrance->getType();
    }
}