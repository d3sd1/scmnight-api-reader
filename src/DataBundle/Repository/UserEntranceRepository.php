<?php
namespace DataBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserEntranceRepository extends EntityRepository
{
    public function getEntranceType($user)
    {
        $usr = $this->createQueryBuilder('pe')
            ->where('pe.user=:user')
            ->setParameter('user', $user)
            ->orderBy('pe.date', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $entranceType = "JOIN";
        if($usr != null)
        {
            switch($usr->getType())
            {
                case 'JOIN':
                    $entranceType = "LEAVE";
                break;
                case 'LEAVE':
                    $entranceType = "JOIN";
                break;
            }
        }
        return $entranceType;
    }
}