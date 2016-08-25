<?php

use yii\db\Migration;

/**
 * Handles the creation for table `copmany_service_type`.
 */
class m160825_123257_create_company_service_type_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        $this->createTable( '{{%company_service_type}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'company_id' => 'INT(10) UNSIGNED NOT NULL',
            'type' => 'SMALLINT(6) NOT NULL DEFAULT \'1\'',
        ], $tableOptions_mysql );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute( 'SET foreign_key_checks = 0' );
        $this->dropTable('{{%company_service_type}}');
        $this->execute( 'SET foreign_key_checks = 1;' );
    }
}
