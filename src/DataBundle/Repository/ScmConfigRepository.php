<?php
namespace DataBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;

class ScmConfigRepository extends EntityRepository
{
    public function loadConfig($conf)
    {
        $configORM = $this->createQueryBuilder('c')
            ->where('c.config = :configname')
            ->setParameter('configname', $conf)
            ->getQuery()
            ->getOneOrNullResult();
        if (null === $configORM) {
            $message = sprintf(
                'Unable to find any config name by this one.',
                $conf
            );
            throw new Exception($message);
        }

        return $configORM->getValue();
    }
}