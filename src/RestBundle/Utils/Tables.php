<?php

namespace RestBundle\Utils;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Tables
{

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function generateTableResponse($params, $bundleName, $mainOrder)
    {
        $limit = (int) @$params['size'];
        $pageNumber = (int) @$params['pageNumber'];
        if ($limit == 0 || $limit > 1000)
        {
            $limit = 10;
        }
        $offset = $pageNumber * $limit;

        $mainTableName = $this->em->getClassMetadata($bundleName)->table["name"];
        $qb = $this->em->getRepository($bundleName)->createQueryBuilder($mainTableName);
        $query = $qb;

        /*
         * Obtener datos de todas las entidades asociadas para ordenar / buscar
         */
        $ordering = array_key_exists('order', $params) && count($params["order"]) > 0;
        $searching = array_key_exists('search', $params);
        if ($ordering || $searching)
        {
            $columns = $this->em->getClassMetadata($bundleName)->getFieldNames();
            $fkColumns = $this->em->getClassMetadata($bundleName)->getAssociationNames();
            foreach ($fkColumns as $fkColumn)
            {
                $targetClass = $this->em->getClassMetadata($bundleName)->getAssociationTargetClass($fkColumn);
                $targetName = $this->em->getClassMetadata($targetClass)->getTableName();
                $targetColumns = $this->em->getClassMetadata($targetClass)->getFieldNames();
                $primaryKey = $this->em->getClassMetadata($bundleName)->getAssociationMapping($fkColumn)["joinColumns"][0]["referencedColumnName"];
                $query = $query->join($targetClass, $targetName, "WITH", $mainTableName . "." . $fkColumn . "=" . $targetName . "." . $primaryKey);
            }
        }
        /* Default order */
        $query->orderBy($mainTableName . "." . $mainOrder["column"], $mainOrder["dir"]);

        if ($ordering)
        {
            $dir = strtoupper($params['order']["dir"]);
            $givenCol = $params['order']["col"];
            if(strpos($givenCol, '.') !== false)
            {
                $tableInfo = explode(".", $givenCol);
            }
            else
            {
                $tableInfo = array(0 => $mainTableName, 1 => $givenCol);
            }

            $columnInfo = array_pop($tableInfo);

            /* Comprobar si es de la tabla */
            if (array_search($columnInfo, $columns) !== FALSE && array_search($mainTableName, $tableInfo) !== FALSE)
            {
                $query->orderBy($mainTableName . "." . $columnInfo, $dir);
            }
            elseif (isset($targetColumns) && array_search($columnInfo, $targetColumns) !== FALSE)
            {
                $query->orderBy($targetName . "." . $columnInfo, $dir);
            }
        }


        if ($searching)
        {
            foreach ($columns as $column)
            {
                $query->orWhere($qb->expr()->like($mainTableName . '.' . $column, ':search'));
            }

            if (isset($targetColumns))
            {
                foreach ($targetColumns as $targetColumn)
                {
                    $query->orWhere($qb->expr()->like($targetName . '.' . $targetColumn, ':search'));
                }
            }
            $query->setParameter('search', '%' . $params['search'] . '%');
            $totalRows = count($query->getQuery()->getResult());
        }
        else
        {
            $totalRows = count($this->em
                            ->getRepository($bundleName)
                            ->findAll());
        }

        $rows = $query
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        $response = array(
            "pageNumber" => $pageNumber,
            "data"       => $rows,
            "totalRows"  => $totalRows
        );

        return $response;
    }

    public function paramsIncorrect()
    {
        throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Params are incorrect');
    }

}
