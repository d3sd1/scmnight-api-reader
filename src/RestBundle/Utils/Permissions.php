<?php

namespace RestBundle\Utils;
use DataBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class Permissions
{
    private $em;
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    
    function hasPermission(String $permission, User $user)
    {
        $userPermissions = $this->em->getRepository('DataBundle:UserPermissions')->findBy(array('user' => $user));
        if(count($userPermissions) === 0)
        {
            return false;
        }
        foreach($userPermissions as $userPermission)
        {
            if($userPermission->getPermission()->getAction() === $permission)
            {
                return true;
            }
        }
        return false;
    }

}
