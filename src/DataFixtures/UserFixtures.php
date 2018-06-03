<?php

namespace DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use DataBundle\Entity\User;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
class UserFixtures implements ORMFixtureInterface {

    public function load(ObjectManager $manager) {
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail('rango' . $i . '@gmail.com');
            $user->setPassword('$2a$08$jHZj/wJfcVKlIwr5AvR78euJxYK7Ku5kURNhNx.7.CSIJ3Pq6LEPC');
            $user->setFirstname("rank {$i}");
            $user->setLastname("rank {$i}");
            $user->setDni("1234567{$i}A");
            $user->setAddress("C/ Test");
            $user->setTelephone("6786858475");
            $user->setLangCode("es");
            $manager->persist($user);
        }
        $manager->flush();
    }

}
