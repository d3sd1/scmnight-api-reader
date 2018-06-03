<?php
namespace DataBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ClientEntranceRepository extends EntityRepository
{
    public function getEntranceType($dni)
    {
        $usr = $this->createQueryBuilder('pe')
            ->where('pe.client=:dni')
            ->setParameter('dni', $dni)
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
                case 'DENIED_FULL':
                    $entranceType = "JOIN";
                break;
                case 'DENIED_CONFLICTIVE':
                    $entranceType = "JOIN";
                break;
                case 'FORCED_ACCESS':
                    $entranceType = "LEAVE";
                break;
            }
        }
        return $entranceType;
    }
}