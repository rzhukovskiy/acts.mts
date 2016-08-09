<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        $tableOptions_mssql = "";
        $tableOptions_pgsql = "";
        $tableOptions_sqlite = "";
        /* MYSQL */
        if (!in_array('company', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%company}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'parent_id' => 'INT(10) UNSIGNED NULL',
                    'name' => 'VARCHAR(45) NOT NULL',
                    'address' => 'VARCHAR(255) NULL',
                    'phone' => 'VARCHAR(255) NULL',
                    'contact' => 'VARCHAR(255) NULL',
                    'type' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
                    'contract' => 'VARCHAR(255) NULL',
                    'act_header' => 'TEXT NULL',
                    'is_split' => 'TINYINT(1) UNSIGNED NOT NULL',
                    'is_infected' => 'TINYINT(1) UNSIGNED NOT NULL',
                    'is_main' => 'TINYINT(1) UNSIGNED NOT NULL',
                    'is_sign' => 'TINYINT(1) UNSIGNED NOT NULL',
                    'status' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
                    'created_at' => 'INT(11) NOT NULL',
                    'updated_at' => 'INT(11) NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        /* MYSQL */
        if (!in_array('user', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%user}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'username' => 'VARCHAR(255) NOT NULL',
                    'role' => 'SMALLINT(6) NOT NULL',
                    'company_id' => 'INT(10) UNSIGNED NULL',
                    'auth_key' => 'VARCHAR(32) NOT NULL',
                    'password_hash' => 'VARCHAR(255) NOT NULL',
                    'password_reset_token' => 'VARCHAR(255) NULL',
                    'email' => 'VARCHAR(255) NOT NULL',
                    'status' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
                    'created_at' => 'INT(11) NOT NULL',
                    'updated_at' => 'INT(11) NOT NULL',
                ], $tableOptions_mysql);
            }
        }


        $this->createIndex('idx_UNIQUE_name_83_00','{{%company}}','name',1);
        $this->createIndex('idx_address_83_01','{{%company}}','address',0);
        $this->createIndex('idx_parent_id_83_02','{{%company}}','parent_id',0);
        $this->createIndex('idx_UNIQUE_username_85_03','{{%user}}','username',1);
        $this->createIndex('idx_UNIQUE_email_85_04','{{%user}}','email',1);
        $this->createIndex('idx_UNIQUE_password_reset_token_85_05','{{%user}}','password_reset_token',1);
        $this->createIndex('idx_company_id_85_06','{{%user}}','company_id',0);

        $this->execute('SET foreign_key_checks = 0');
        $this->addForeignKey('fk_acts_company_83_00','{{%company}}', 'parent_id', '{{%company}}', 'id', 'CASCADE', 'CASCADE' );
        $this->addForeignKey('fk_acts_company_85_01','{{%user}}', 'company_id', '{{%company}}', 'id', 'CASCADE', 'CASCADE' );
        $this->execute('SET foreign_key_checks = 1;');
    }

    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->execute('DROP TABLE IF EXISTS `acts_company`');
        $this->execute('SET foreign_key_checks = 1;');
        $this->execute('SET foreign_key_checks = 0');
        $this->execute('DROP TABLE IF EXISTS `acts_user`');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
