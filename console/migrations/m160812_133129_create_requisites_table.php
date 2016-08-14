<?php

use yii\db\Migration;

class m160812_133129_create_requisites_table extends Migration
{
    public function up()
    {
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        $this->createTable( '{{%requisites}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'company_id' => 'INT(10) UNSIGNED NOT NULL',
            'type' => 'SMALLINT(6) NOT NULL DEFAULT \'1\'',
            'contract' => 'VARCHAR(255) NOT NULL',
            'header' => 'VARCHAR(1000) NULL',
        ], $tableOptions_mysql );
    }

    public function down()
    {
        $this->execute( 'SET foreign_key_checks = 0' );
        $this->dropTable('{{%requisites}}');
        $this->execute( 'SET foreign_key_checks = 1;' );
    }
}
