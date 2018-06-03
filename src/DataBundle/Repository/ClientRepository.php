<?php
namespace DataBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{
    public function isConflictive($dni)
    {
        $usr = $this->createQueryBuilder('p')
            ->where('p.dni=:dni')
            ->setParameter('dni', $dni)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        return $usr == null ? false:$usr->getConflictive();
    }
}