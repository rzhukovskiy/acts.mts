<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%company}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'parent_id' => 'INT(10) UNSIGNED NULL',
            'name' => 'VARCHAR(255) NOT NULL',
            'address' => 'VARCHAR(255) NULL',
            'phone' => 'VARCHAR(255) NULL',
            'director' => 'VARCHAR(255) NULL',
            'type' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
            'is_split' => 'TINYINT(1) UNSIGNED NOT NULL',
            'is_infected' => 'TINYINT(1) UNSIGNED NOT NULL',
            'is_main' => 'TINYINT(1) UNSIGNED NOT NULL',
            'is_sign' => 'TINYINT(1) UNSIGNED NOT NULL',
            'status' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
        ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");


        $this->createTable('{{%user}}', [
            'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'username' => 'VARCHAR(255) NOT NULL',
            'role' => 'SMALLINT(6) NOT NULL',
            'company_id' => 'INT(10) UNSIGNED NULL',
            'auth_key' => 'VARCHAR(32) NOT NULL',
            'password_hash' => 'VARCHAR(255) NOT NULL',
            'password_reset_token' => 'VARCHAR(255) NULL',
            'email' => 'VARCHAR(255) NULL',
            'status' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
        ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");


        $this->createIndex('idx_UNIQUE_name_83_00','{{%company}}','name',1);
        $this->createIndex('idx_address_83_01','{{%company}}','address',0);
        $this->createIndex('idx_parent_id_83_02','{{%company}}','parent_id',0);
        $this->createIndex('idx_UNIQUE_username_85_03','{{%user}}','username',1);
        $this->createIndex('idx_UNIQUE_email_85_04','{{%user}}','email',1);
        $this->createIndex('idx_UNIQUE_password_reset_token_85_05','{{%user}}','password_reset_token',1);
        $this->createIndex('idx_company_id_85_06','{{%user}}','company_id',0);
    }

    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%company}}');
        $this->dropTable('{{%user}}');
        $this->execute('SET foreign_key_checks = 1');
    }
}
