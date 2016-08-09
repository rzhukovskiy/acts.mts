<?php

use yii\db\Migration;

class m160809_134206_create_employee_tables extends Migration
{
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        if (!in_array('employee', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%employee}}', [
                    'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'employee_group_id' => 'INT(10) UNSIGNED NOT NULL',
                    'username' => 'VARCHAR(32) NOT NULL',
                    'password' => 'VARCHAR(32) NOT NULL',
                    'salt' => 'VARCHAR(32) NOT NULL',
                    'create_date' => 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ',
                    'role' => 'VARCHAR(8) NOT NULL DEFAULT \'employee\'',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('employee_group', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%employee_group}}', [
                    'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'name' => 'VARCHAR(32) NOT NULL',
                    'manage' => 'TINYINT(1) NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('employee_group_archive_request_type', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%employee_group_archive_request_type}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'employee_group_id' => 'INT(10) UNSIGNED NOT NULL',
                    'service' => 'TINYINT(1) NOT NULL',
                    'tires' => 'TINYINT(1) NOT NULL',
                    'wash' => 'TINYINT(1) NOT NULL',
                    'company' => 'TINYINT(1) NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('employee_group_request_type', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%employee_group_request_type}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'employee_group_id' => 'INT(10) UNSIGNED NOT NULL',
                    'service' => 'TINYINT(1) NOT NULL',
                    'tires' => 'TINYINT(1) NOT NULL',
                    'wash' => 'TINYINT(1) NOT NULL',
                    'company' => 'TINYINT(1) NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        $this->createIndex('idx_username_78_00','{{%employee}}','username',0);
        $this->createIndex('idx_employee_group_id_78_01','{{%employee}}','employee_group_id',0);
        $this->createIndex('idx_UNIQUE_employee_group_id_79_02','{{%employee_group_archive_request_type}}','employee_group_id',1);
        $this->createIndex('idx_UNIQUE_employee_group_id_79_03','{{%employee_group_request_type}}','employee_group_id',1);
    }

    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%employee}}');
        $this->dropTable('{{%employee_group}}');
        $this->dropTable('{{%employee_group_archive_request_type}}');
        $this->dropTable('{{%employee_group_request_type}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
