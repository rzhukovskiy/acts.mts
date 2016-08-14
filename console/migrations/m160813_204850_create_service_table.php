<?php

use yii\db\Migration;

/**
 * Handles the creation for table `service`.
 */
class m160813_204850_create_service_table extends Migration
{
    public function up()
    {
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        $this->createTable( '{{%service}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'is_fixed' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'type' => 'SMALLINT(6) NOT NULL DEFAULT \'1\'',
            'description' => 'VARCHAR(255) NOT NULL',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
        ], $tableOptions_mysql );

        $this->createTable( '{{%company_service}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'company_id' => 'INT(10) UNSIGNED NOT NULL',
            'service_id' => 'INT(10) UNSIGNED NOT NULL',
            'type_id' => 'INT(10) UNSIGNED NOT NULL',
            'price' => 'INT(10) NOT NULL DEFAULT \'0\'',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
        ], $tableOptions_mysql );
    }

    public function down()
    {
        $this->execute( 'SET foreign_key_checks = 0' );
        $this->dropTable('{{%service}}');
        $this->dropTable('{{%company_service}}');
        $this->execute( 'SET foreign_key_checks = 1;' );
    }
}
