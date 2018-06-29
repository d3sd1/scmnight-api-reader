<?php

namespace DataFixtures;

use DataBundle\Entity\Permission;
use DataBundle\Entity\UserPermissions;
use Doctrine\Common\Persistence\ObjectManager;
use DataBundle\Entity\User;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

class UserFixtures implements ORMFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@scmnight.com');
        $user->setPassword('$2a$08$jHZj/wJfcVKlIwr5AvR78euJxYK7Ku5kURNhNx.7.CSIJ3Pq6LEPC');
        $user->setFirstname("SCK");
        $user->setLastname("NIGHT");
        $user->setDni("53457069D");
        $user->setAddress("C/ Test");
        $user->setTelephone("6786858475");
        $user->setLangCode("es");
        $manager->persist($user);
        $manager->flush();
        $permissions = $manager->getRepository('DataBundle:Permission')->findAll();
        foreach ($permissions as $permission) {
            $userPermission = new UserPermissions();
            $userPermission->setUser($user);
            $userPermission->setPermission($permission);
            $manager->persist($userPermission);
            $manager->flush();
        }
    }

}
