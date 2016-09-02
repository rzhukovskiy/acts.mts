<?php

use yii\db\Migration;

/**
 * Handles the creation for table `department`.
 */
class m160902_122943_create_department_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        $this->createTable( '{{%department}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'name' => 'VARCHAR(255) NOT NULL',
            'role' => 'SMALLINT(6) NOT NULL',
            'status' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
        ], $tableOptions_mysql );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute( 'SET foreign_key_checks = 0' );
        $this->dropTable('{{%department}}');
        $this->execute( 'SET foreign_key_checks = 1;' );
    }
}
