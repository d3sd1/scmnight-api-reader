<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManager;
class AppTokenProvider implements UserProviderInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getAppToken($authTokenHeader)
    {
        return $this->em->getRepository('AppBundle:AppToken')->findOneBy(array('value' => $authTokenHeader));
    }

    public function loadUserByUsername($email)
    {
        
    }

    public function refreshUser(UserInterface $user)
    {
        
    }

    public function supportsClass($class)
    {
        
    }
}