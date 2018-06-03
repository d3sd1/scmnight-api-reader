<?php

declare(strict_types = 1);

namespace Application\DataMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180406111451 extends AbstractMigration {

    public function up(Schema $schema) {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("int")');
        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("string")');
        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("boolean")');
        $this->addSql('INSERT INTO config_type (TYPE) VALUES ("double")');
        $this->addSql('INSERT INTO users_entrance_type (TYPE) VALUES ("JOIN")');
        $this->addSql('INSERT INTO users_entrance_type (TYPE) VALUES ("LEAVE")');
        $this->addSql('INSERT INTO clients_entrance_type (TYPE) VALUES ("JOIN")');
        $this->addSql('INSERT INTO clients_entrance_type (TYPE) VALUES ("LEAVE")');
        $this->addSql('INSERT INTO clients_entrance_type (TYPE) VALUES ("DENIED_FULL")');
        $this->addSql('INSERT INTO clients_entrance_type (TYPE) VALUES ("DENIED_CONFLICTIVE")');
        $this->addSql('INSERT INTO clients_entrance_type (TYPE) VALUES ("FORCED_ACCESS")');
        $this->addSql('INSERT INTO users_manage_type (TYPE) VALUES ("ADD")');
        $this->addSql('INSERT INTO users_manage_type (TYPE) VALUES ("EDIT")');
        $this->addSql('INSERT INTO users_manage_type (TYPE) VALUES ("DELETE")');
        $this->addSql('INSERT INTO users_genders (gender) VALUES ("M")');
        $this->addSql('INSERT INTO users_genders (gender) VALUES ("F")');
        $this->addSql('INSERT INTO users_genders (gender) VALUES ("NA")');
        $this->addSql('INSERT INTO permissions (action) VALUES ("VIEW_USERS")');
        $this->addSql('INSERT INTO pages_permissions (permission_id,page_url) VALUES (1,"/rest/users/table/all")');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("api_rate_limit_interval_s","20",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("api_rate_limit_rate","10",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("maxPersonsInRoom","1000",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("disco_name","Mi discoteca",2)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("recover_code_seconds_expire","600",1)');
        $this->addSql('insert  into `scm_config`(`config`,`value`) values  ("version","0.7.1")');
    }

    public function down(Schema $schema) {
    }

}
