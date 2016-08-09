<?php

use yii\db\Migration;

class m160809_132643_create_request_tables extends Migration
{
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        if (!in_array('request', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'new' => 'TINYINT(1) NOT NULL DEFAULT \'1\'',
                    'name' => 'VARCHAR(128) NULL',
                    'address_timezone' => 'VARCHAR(32) NULL',
                    'address_index' => 'VARCHAR(32) NULL',
                    'address_city' => 'VARCHAR(32) NULL',
                    'address_street' => 'VARCHAR(128) NULL',
                    'address_house' => 'VARCHAR(16) NULL',
                    'address_phone' => 'VARCHAR(255) NULL',
                    'address_mail' => 'VARCHAR(1024) NULL',
                    'time_from' => 'VARCHAR(32) NULL',
                    'time_to' => 'VARCHAR(32) NULL',
                    'director_name' => 'VARCHAR(256) NULL',
                    'director_email' => 'VARCHAR(32) NULL',
                    'director_phone' => 'VARCHAR(16) NULL',
                    'doc_name' => 'VARCHAR(256) NULL',
                    'doc_email' => 'VARCHAR(32) NULL',
                    'doc_phone' => 'VARCHAR(16) NULL',
                    'next_communication_date' => 'DATETIME NULL',
                    'payment_day' => 'VARCHAR(64) NULL',
                    'email' => 'VARCHAR(128) NULL',
                    'agreement_number' => 'VARCHAR(255) NULL',
                    'agreement_date' => 'DATE NULL',
                    'agreement_file' => 'VARCHAR(256) NULL',
                    'status' => 'MEDIUMTEXT NULL',
                    'mail_number' => 'VARCHAR(255) NULL',
                    'state' => 'TINYINT(1) UNSIGNED NOT NULL',
                    'employee_group_id' => 'INT(10) UNSIGNED NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_comments', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_comments}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_id' => 'INT(10) UNSIGNED NOT NULL',
                    'employee_id' => 'INT(10) UNSIGNED NOT NULL',
                    'text' => 'TEXT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_company', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_company}}', [
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'contact_name' => 'VARCHAR(256) NULL',
                    'phone' => 'VARCHAR(16) NULL',
                    'email' => 'VARCHAR(32) NULL',
                    'city' => 'VARCHAR(512) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_company_autopark', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_company_autopark}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'model' => 'VARCHAR(64) NULL',
                    'type' => 'VARCHAR(128) NULL',
                    'amount' => 'SMALLINT(5) UNSIGNED NULL',
                    'price_outside' => 'VARCHAR(64) NULL',
                    'price_inside' => 'VARCHAR(64) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_company_driver', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_company_driver}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'model' => 'VARCHAR(64) NULL',
                    'type' => 'VARCHAR(128) NULL',
                    'fio' => 'VARCHAR(256) NULL',
                    'phone' => 'VARCHAR(255) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_company_list_auto', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_company_list_auto}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'model' => 'VARCHAR(64) NULL',
                    'type' => 'VARCHAR(128) NULL',
                    'state_number' => 'VARCHAR(8) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_done', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_done}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_id' => 'INT(10) UNSIGNED NOT NULL',
                    'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_employee', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_employee}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_id' => 'INT(10) UNSIGNED NOT NULL',
                    'position' => 'VARCHAR(32) NULL',
                    'name' => 'VARCHAR(256) NULL',
                    'email' => 'VARCHAR(32) NULL',
                    'phone' => 'VARCHAR(255) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_price', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_price}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'type' => 'VARCHAR(128) NULL',
                    'price_outside' => 'VARCHAR(64) NULL',
                    'price_inside' => 'VARCHAR(64) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_process', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_process}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_id' => 'INT(10) UNSIGNED NOT NULL',
                    'employee_group_id' => 'INT(10) UNSIGNED NOT NULL',
                    'updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_process_employee', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_process_employee}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'employee_id' => 'INT(10) UNSIGNED NOT NULL',
                    'request_id' => 'INT(10) UNSIGNED NOT NULL',
                    'created' => 'TIMESTAMP NULL',
                    'finished' => 'TIMESTAMP NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_refused', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_refused}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_id' => 'INT(10) UNSIGNED NOT NULL',
                    'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_service', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_service}}', [
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'official_dealer' => 'TEXT NULL',
                    'nonofficial_dealer' => 'TEXT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_service_serve_organisation', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_service_serve_organisation}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(11) NOT NULL',
                    'name' => 'VARCHAR(256) NULL',
                    'phone' => 'VARCHAR(16) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_service_work_rate', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_service_work_rate}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(11) NOT NULL',
                    'work_name' => 'VARCHAR(256) NULL',
                    'rate' => 'VARCHAR(64) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_tires', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_tires}}', [
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                    'service_mounting' => 'TINYINT(1) NULL',
                    'service_tires_sale' => 'TINYINT(1) NULL',
                    'service_disk_sale' => 'TINYINT(1) NULL',
                    'serve_car' => 'TINYINT(1) NULL',
                    'serve_truck' => 'TINYINT(1) NULL',
                    'serve_tech' => 'TINYINT(1) NULL',
                    'sale_for_car' => 'TINYINT(1) NULL',
                    'sale_for_truck' => 'TINYINT(1) NULL',
                    'sale_for_tech' => 'TINYINT(1) NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_tires_serve_organisation', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_tires_serve_organisation}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(11) NOT NULL',
                    'name' => 'VARCHAR(256) NULL',
                    'phone' => 'VARCHAR(16) NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_wash', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_wash}}', [
                    'request_ptr_id' => 'INT(10) UNSIGNED NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        if (!in_array('request_wash_serve_organisation', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%request_wash_serve_organisation}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'request_ptr_id' => 'INT(11) NOT NULL',
                    'name' => 'VARCHAR(256) NULL',
                    'phone' => 'VARCHAR(16) NULL',
                ], $tableOptions_mysql);
            }
        }


        $this->createIndex('idx_name_55_00','{{%request_employee}}','name',0);
        $this->createIndex('idx_employee_group_id_56_01','{{%request_process}}','employee_group_id',0);
        $this->createIndex('idx_employee_id_56_02','{{%request_process_employee}}','employee_id',0);
        $this->createIndex('idx_request_id_56_03','{{%request_process_employee}}','request_id',0);
        $this->createIndex('idx_request_ptr_id_57_04','{{%request_service}}','request_ptr_id',0);
        $this->createIndex('idx_request_ptr_id_57_05','{{%request_service_serve_organisation}}','request_ptr_id',0);
        $this->createIndex('idx_request_ptr_id_58_06','{{%request_service_work_rate}}','request_ptr_id',0);
        $this->createIndex('idx_request_ptr_id_58_07','{{%request_tires}}','request_ptr_id',0);
        $this->createIndex('idx_request_ptr_id_59_08','{{%request_tires_serve_organisation}}','request_ptr_id',0);
        $this->createIndex('idx_request_ptr_id_59_09','{{%request_wash}}','request_ptr_id',0);
        $this->createIndex('idx_request_ptr_id_59_10','{{%request_wash_serve_organisation}}','request_ptr_id',0);

        $this->execute('SET foreign_key_checks = 0');
        $this->addForeignKey('fk_request_57_00','{{%request_service}}', 'request_ptr_id', '{{%request}}', 'id', 'CASCADE', 'CASCADE' );
        $this->addForeignKey('fk_request_58_01','{{%request_tires}}', 'request_ptr_id', '{{%request}}', 'id', 'CASCADE', 'CASCADE' );
        $this->addForeignKey('fk_request_59_02','{{%request_wash}}', 'request_ptr_id', '{{%request}}', 'id', 'CASCADE', 'CASCADE' );
        $this->execute('SET foreign_key_checks = 1;');
    }

    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%request}}');
        $this->dropTable('{{%request_comments}}');
        $this->dropTable('{{%request_company}}');
        $this->dropTable('{{%request_company_autopark}}');
        $this->dropTable('{{%request_company_driver}}');
        $this->dropTable('{{%request_company_list_auto}}');
        $this->dropTable('{{%request_done}}');
        $this->dropTable('{{%request_employee}}');
        $this->dropTable('{{%request_price}}');
        $this->dropTable('{{%request_process}}');
        $this->dropTable('{{%request_process_employee}}');
        $this->dropTable('{{%request_refused}}');
        $this->dropTable('{{%request_service}}');
        $this->dropTable('{{%request_service_serve_organisation}}');
        $this->dropTable('{{%request_service_work_rate}}');
        $this->dropTable('{{%request_tires}}');
        $this->dropTable('{{%request_tires_serve_organisation}}');
        $this->dropTable('{{%request_wash}}');
        $this->dropTable('{{%request_wash_serve_organisation}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
