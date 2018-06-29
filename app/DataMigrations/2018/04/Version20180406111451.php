<?php

declare(strict_types=1);

namespace Application\DataMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180406111451 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("int")');
        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("string")');
        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("boolean")');
        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("double")');
        $this->addSql('INSERT INTO users_entrance_type (name) VALUES ("JOIN")');
        $this->addSql('INSERT INTO users_entrance_type (name) VALUES ("LEAVE")');
        $this->addSql('INSERT INTO clients_entrance_type (name) VALUES ("JOIN")');
        $this->addSql('INSERT INTO clients_entrance_type (name) VALUES ("LEAVE")');
        $this->addSql('INSERT INTO clients_entrance_type (name) VALUES ("DENIED_FULL")');
        $this->addSql('INSERT INTO clients_entrance_type (name) VALUES ("DENIED_CONFLICTIVE")');
        $this->addSql('INSERT INTO clients_entrance_type (name) VALUES ("FORCED_ACCESS")');
        $this->addSql('INSERT INTO users_manage_type (TYPE) VALUES ("ADD")');
        $this->addSql('INSERT INTO users_manage_type (TYPE) VALUES ("EDIT")');
        $this->addSql('INSERT INTO users_manage_type (TYPE) VALUES ("DELETE")');
        $this->addSql('INSERT INTO genders (name) VALUES ("M")');
        $this->addSql('INSERT INTO genders (name) VALUES ("F")');
        $this->addSql('INSERT INTO genders (name) VALUES ("NA")');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("api_rate_limit_interval_s","20",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("api_rate_limit_rate","10",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("maxPersonsInRoom","1000",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("disco_name","Mi discoteca",2)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("recover_code_seconds_expire","600",1)');
        $this->addSql('insert  into `scm_config`(`config`,`value`) values  ("version","0.7.1")');
        $this->addSql('insert  into `clients_entrance_pricing`(`name`,`price`) values  ("Gratis por RRPP","0"),("Entrada por RRPP","5"),("Entrada general","10"),("Invitado","0"),("Empleado","0")');
        $this->addSql('insert  into `clients_ban_type`(`name`) values ("Altercados"), ("Estado de embriaguez"), ("Consumo de drogas"), ("Agresiones")');
        $this->addSql('insert  into `nationalities`(`name`) values ("ES")');
        $this->addSql('insert  into `permissions_list`(`list_key_name`) values ("RESPONSABLE"),("SECURITY"),("WAITER"),("MARKETING"),("RRPP_BOSS"),("RRPP"),("SCM")');
        $this->addSql("insert  into `permissions`(`action`) values 
                            ('VIEW_DASHBOARD'),
                            ('MANAGE_ROOM_BAR'),
                            ('MANAGE_PROFILE'),
                            ('MANAGE_USERS'),
                            ('VIEW_ALL_USERS'),
                            ('VIEW_ROOM_USERS'),
                            ('MANAGE_ROOM_STAGE'),
                            ('MANAGE_ROOM_RESERVED'),
                            ('VIEW_ROOM_CLIENTS'),
                            ('VIEW_ALL_CLIENTS'),
                            ('VIEW_MONETIZATION'),
                            ('VIEW_STATS'),
                            ('MANAGE_STOCK'),
                            ('MANAGE_MARKETING_PROMOS'),
                            ('MANAGE_MARKETING_EVENTS'),
                            ('MANAGE_MARKETING_PARTIES'),
                            ('MANAGE_CONFIG'),
                            ('SET_USER_CONFLICTIVE'),
                            ('LOGOUT');");
    }

    public function down(Schema $schema)
    {
    }

}
