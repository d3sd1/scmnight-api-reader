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
        $this->addSql('INSERT INTO permission_list_manage_type (name) VALUES ("ADD")');
        $this->addSql('INSERT INTO permission_list_manage_type (name) VALUES ("EDIT")');
        $this->addSql('INSERT INTO permission_list_manage_type (name) VALUES ("DELETE")');
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
        $this->addSql('insert  into `clients_ban_type`(`name`) values 
                      ("DISPUTES"), ("TOO_DRUNKED"), ("DRUGS_CONSUME"), ("AGGRESSIONS")');
        $this->addSql('insert  into `nationalities`(`name`) values ("ES")');
        $this->addSql('insert  into `permissions_list`(id,`list_key_name`) values (1,"BOSS"),(6,"SECURITY"),(3,"WAITER"),(4,"MARKETING"),(5,"RRPP_BOSS"),(2,"RRPP")');
        $this->addSql('insert  into `custom_translate_available_langs`(id,`lang_key`) values (1,"es"),(2,"en")');
        $this->addSql("insert  into `user_chat_status`(chat_status) values ('IDLE'),('OFFLINE'),('ONLINE');");
        /* LISTAS DE PERMISOS PARA PERMISOS - GRUPOS DE PERMISOS. AL JEFE LE METEMOS T0DOS

        TODO
        $permissions =$em->getRepository('DataBundle:Permission')->findAll();
        foreach ($permissions as $permission) {
            $userPermission = new UserPermissions();
            $userPermission->setUser($user);
            $userPermission->setPermission($permission);
            $em->persist($userPermission);
            $em->flush();
        }*/
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
                            ('VIEW_SERVER_STATUS'),
                            ('SELL_STOCK'),
                            ('CHANGE_LOGO'),
                            ('MANAGE_PERMISSIONS'),
                            ('MANAGE_TRANSLATES'),
                            ('MANAGE_STOCK_TYPES'),
                            ('RECOVER_PASSWORD'),
                            ('LOGOUT');");
        $this->addSql('insert  into `permissions_lists`(`id_permission_id`,`id_list_id`) values 
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,6),
(2,1),
(2,2),
(2,3),
(2,4),
(2,5),
(2,6),
(3,1),
(4,1),
(4,6),
(5,1),
(5,6),
(6,1),
(6,2),
(6,3),
(6,4),
(6,5),
(6,6),
(7,1),
(7,4),
(7,6),
(8,1),
(8,4),
(9,1),
(9,4),
(9,5),
(9,6),
(10,1),
(10,4),
(11,1),
(11,4),
(12,1),
(12,4),
(13,1),
(13,4),
(14,1),
(15,1),
(15,6),
(16,1),
(16,2),
(16,5),
(17,1),
(17,6),
(18,1),
(18,4),
(19,1),
(20,1),
(20,3),
(21,1),
(22,1),
(23,1),
(24,1),
(24,4),
(25,1),
(25,2),
(25,3),
(25,4),
(25,5),
(25,6),
(26,1),
(26,2),
(26,3),
(26,4),
(26,5),
(26,6);');
        $this->addSql("
insert  into `custom_translate`(`key_id`,`lang_key_id`,`value`) values 
('CONFLICT.AGGRESSIONS',1,'Agresiones'),
('CONFLICT.AGGRESSIONS',2,'Agressions'),
('CONFLICT.DISPUTES',1,'Disputas'),
('CONFLICT.DISPUTES',2,'Disputes'),
('CONFLICT.DRUGS_CONSUMING',1,'Consumo de drogas'),
('CONFLICT.DRUGS_CONSUMING',2,'Drugs consuming'),
('CONFLICT.TOO_DRUNKED',1,'Consumo excesivo de alcohol'),
('EMAIL.POWEREDBY',1,'Local manejado por '),
('EMAIL.POWEREDBY',2,'Powered by '),
('EMAIL.RECOVERPASS.CONTENT',1,'Para proceder a recuperar tu cuenta, ingresa el código:'),
('EMAIL.RECOVERPASS.CONTENT',2,'Put this code inside the new form:'),
('EMAIL.RECOVERPASS.SENDNEW.CONTENT',1,'Aquí tienes tu nueva contraseña, podrás cambiarla una vez accedas al panel: '),
('EMAIL.RECOVERPASS.SENDNEW.CONTENT',2,'There goes your new password, you would change it after login: '),
('EMAIL.RECOVERPASS.SENDNEW.TITLE',1,'Nueva contraseña'),
('EMAIL.RECOVERPASS.SENDNEW.TITLE',2,'New password'),
('EMAIL.RECOVERPASS.TITLE',1,'Recupera tu contraseña'),
('EMAIL.RECOVERPASS.TITLE',2,'Recover password'),
('PERMISSION.IND.CHANGE_LOGO',1,'Cambiar logotipo'),
('PERMISSION.IND.LOGOUT',1,'Cerrar sesión'),
('PERMISSION.IND.MANAGE_CONFIG',1,'Modificar configuración'),
('PERMISSION.IND.MANAGE_MARKETING_EVENTS',1,'Modificar eventos (Marketing)'),
('PERMISSION.IND.MANAGE_MARKETING_PARTIES',1,'Modificar fiestas (marketing)'),
('PERMISSION.IND.MANAGE_MARKETING_PROMOS',1,'Modificar promociones (Marketing)'),
('PERMISSION.IND.MANAGE_PERMISSIONS',1,'Modificar grupos de permisos'),
('PERMISSION.IND.MANAGE_PROFILE',1,'Modificar perfil (propio)'),
('PERMISSION.IND.MANAGE_ROOM_CONFLICTS',1,'Modificar tipos de conflicto'),
('PERMISSION.IND.MANAGE_ROOM_RATES',1,'Modificar tarifas'),
('PERMISSION.IND.MANAGE_ROOM_THEME',1,'Modificar tema (web)'),
('PERMISSION.IND.MANAGE_STOCK',1,'Modificar inventario'),
('PERMISSION.IND.MANAGE_STOCK_TYPES',1,'Modificar tipos de inventario'),
('PERMISSION.IND.MANAGE_TRANSLATES',1,'Traducciones'),
('PERMISSION.IND.MANAGE_USERS',1,'Modificar trabajadores'),
('PERMISSION.IND.RECOVER_PASSWORD',1,'Restaurar contraseña (Personal)'),
('PERMISSION.IND.SELL_STOCK',1,'Vender inventario'),
('PERMISSION.IND.SET_CLIENT_INFO',1,'Recoger innformación de usuario en sala'),
('PERMISSION.IND.SET_USER_CONFLICTIVE',1,'Marcar usuario como conflictivo'),
('PERMISSION.IND.VIEW_ALL_CLIENTS',1,'Ver histórico de clientes'),
('PERMISSION.IND.VIEW_ALL_USERS',1,'Ver histórico trabajadores'),
('PERMISSION.IND.VIEW_DASHBOARD',1,'Ver estadísticas de sala (básicas)'),
('PERMISSION.IND.VIEW_MONETIZATION',1,'Ver flujo de caja'),
('PERMISSION.IND.VIEW_ROOM_CLIENTS',1,'Ver clientes en sala'),
('PERMISSION.IND.VIEW_ROOM_USERS',1,'Ver trabajadores en sala'),
('PERMISSION.IND.VIEW_SERVER_STATUS',1,'Ver estado del servidor'),
('PERMISSION.IND.VIEW_STATS',1,'Ver estadísticas'),
('PERMISSION.MARKETING',1,'Marketing'),
('PERMISSION.MARKETING',2,'Marketing'),
('PERMISSION.BOSS',1,'Jefe'),
('PERMISSION.BOSS',2,'Boss'),
('PERMISSION.RRPP',1,'Relaciones públicas'),
('PERMISSION.RRPP',2,'Public relations'),
('PERMISSION.RRPP_BOSS',1,'Jefe de relaciones públicas'),
('PERMISSION.RRPP_BOSS',2,'Public relations boss'),
('PERMISSION.SECURITY',1,'Seguridad'),
('PERMISSION.SECURITY',2,'Security'),
('PERMISSION.WAITER',1,'Camarero'),
('PERMISSION.WAITER',2,'Waiter');
                        ");
    }

    public function down(Schema $schema)
    {
    }

}
