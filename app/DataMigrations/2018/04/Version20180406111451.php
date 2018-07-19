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
        $this->addSql('INSERT INTO users_manage_type (name) VALUES ("ADD")');
        $this->addSql('INSERT INTO users_manage_type (name) VALUES ("EDIT")');
        $this->addSql('INSERT INTO users_manage_type (name) VALUES ("DELETE")');
        $this->addSql('INSERT INTO conflict_reason_manage_type (name) VALUES ("ADD")');
        $this->addSql('INSERT INTO conflict_reason_manage_type (name) VALUES ("EDIT")');
        $this->addSql('INSERT INTO conflict_reason_manage_type (name) VALUES ("DELETE")');
        $this->addSql('INSERT INTO rate_manage_type (name) VALUES ("ADD")');
        $this->addSql('INSERT INTO rate_manage_type (name) VALUES ("EDIT")');
        $this->addSql('INSERT INTO rate_manage_type (name) VALUES ("DELETE")');
        $this->addSql('INSERT INTO genders (name) VALUES ("M")');
        $this->addSql('INSERT INTO genders (name) VALUES ("F")');
        $this->addSql('INSERT INTO genders (name) VALUES ("NA")');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("api_rate_limit_interval_s","20",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("api_rate_limit_rate","10",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("maxPersonsInRoom","1000",1)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("disco_name","Mi discoteca",2)');
        $this->addSql('insert  into `config`(`config`,`value`, dataType) values  ("recover_code_seconds_expire","600",1)');
        $this->addSql('insert  into `extra_config`(`config`,`value`) values  ("base64_logo","")');
        $this->addSql('insert  into `scm_config`(`config`,`value`) values  ("version","0.7.1")');
        $this->addSql('insert  into `clients_entrance_pricing`(trans_es,trans_en,`name`,`price`) values 
                    ("Gratis por RRPP", "Free by RRPP", "RRPP_FREE","0"),("Entrada por RRPP", "Entrance by RRPP", "RRPP_FLYER","5"),("Entrada general", "General entrance", "GENERAL","10"),
                    ("Invitado", "Guest", "GUEST","0"),("Empleado", "Worker", "INTERNAL_WORKER","0")');
        $this->addSql('insert  into `clients_ban_type`(`name`,trans_es,trans_en) values 
                      ("DISPUTES","Altercados","Disputes"), ("TOO_DRUNKED","Estado de embriaguez","Too drunked"), ("DRUGS_CONSUME","Consumo de drogas","Drugs consuming"), ("AGGRESSIONS","Agresiones","Aggressions")');
        $this->addSql('insert  into `nationalities`(`name`) values ("ES")');
        $this->addSql('insert  into `permissions_list`(`list_key_name`) values ("RESPONSABLE"),("SECURITY"),("WAITER"),("MARKETING"),("RRPP_BOSS"),("RRPP"),("SCM")');
        $this->addSql('insert  into `custom_translate_available_langs`(id,`lang_key`) values (1,"es"),(2,"en")');
        $this->addSql("INSERT  INTO `custom_translate`(`key_id`,`value`,`lang_key_id`) VALUES
                        ('CONFLICT.DISPUTES','Disputas',1),
                        ('CONFLICT.DISPUTES','Disputes',2),
                        ('CONFLICT.AGGRESSIONS','Agresiones',1),
                        ('CONFLICT.AGGRESSIONS','Agressions',2),
                        ('CONFLICT.DRUGS_CONSUMING','Consumo de drogas',1),
                        ('CONFLICT.DRUGS_CONSUMING','Drugs consuming',2),
                        ('CONFLICT.TOO_DRUNKED','Consumo excesivo de alcohol',1),
                        ('EMAIL.RECOVERPASS.TITLE','Recupera tu contraseña',2),
                        ('EMAIL.RECOVERPASS.CONTENT','Para proceder a recuperar tu cuenta, ingresa el código:',2)
                        ");
        $this->addSql("insert  into `permissions`(`action`) values 
                            ('VIEW_DASHBOARD'),
                            ('MANAGE_PROFILE'),
                            ('MANAGE_USERS'),
                            ('VIEW_ALL_USERS'),
                            ('VIEW_ROOM_USERS'),
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
                            ('SET_CLIENT_INFO'),
                            ('MANAGE_ROOM_CONFLICTS'),
                            ('MANAGE_ROOM_RATES'),
                            ('MANAGE_ROOM_IMAGE'),
                            ('VIEW_SERVER_STATUS'),
                            ('SELL_STOCK'),
                            ('CHANGE_LOGO'),
                            ('MANAGE_PERMISSIONS'),
                            ('MANAGE_TRANSLATES'),
                            ('MANAGE_STOCK_TYPES'),
                            ('RECOVER_PASSWORD'),
                            ('LOGOUT');");
    }

    public function down(Schema $schema)
    {
    }

}
