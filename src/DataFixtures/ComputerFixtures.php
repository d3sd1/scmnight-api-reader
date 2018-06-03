<?php

namespace DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Computer;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

class ComputerFixtures implements ORMFixtureInterface {

    public function load(ObjectManager $manager) {
        $pcCasa = new Computer();
        $pcCasa->setMac("1C-1B-0D-92-14-E0");
        $pcCasa->setActive(true);
        $pcCasa->setDescription("PC CASA");
        $manager->persist($pcCasa);
        $pcPortatilLan = new Computer();
        $pcPortatilLan->setMac("4C-CC-6A-83-B8-78");
        $pcPortatilLan->setActive(true);
        $pcPortatilLan->setDescription("PC PORTATIL LAN");
        $manager->persist($pcPortatilLan);
        $pcPortatilWifi = new Computer();
        $pcPortatilWifi->setMac("9E-B6-D0-61-59-09");
        $pcPortatilWifi->setActive(true);
        $pcPortatilWifi->setDescription("PC PORTATIL WIFI");
        $manager->persist($pcPortatilWifi);
        $manager->flush();
    }

}
