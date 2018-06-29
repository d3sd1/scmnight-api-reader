<?php
namespace DataBundle\Repository;

use DataBundle\Entity\ClientEntrance;
use DataBundle\Entity\ClientEntranceType;
use Doctrine\ORM\EntityRepository;

class ClientEntranceRepository extends EntityRepository
{
    public function getEntranceType($dni)
    {
        $entrance = $this->createQueryBuilder('pe')
            ->where('pe.client=:dni')
            ->setParameter('dni', $dni)
            ->orderBy('pe.date', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $entranceType = "JOIN";
        if($entrance != null)
        {
            $entranceNamedType = $this->getEntityManager()->getRepository('DataBundle:ClientEntranceType')->findOneBy(["id" => $entrance->getType()->getId()])->getName();
            switch($entranceNamedType)
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
        $entrance = new ClientEntrance();
        $entrance->setType($this->getEntityManager()->getRepository('DataBundle:ClientEntranceType')->findOneBy(["name" => $entranceType]));
        return $entrance;
    }
}