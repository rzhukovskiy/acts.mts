<?php

use yii\db\Migration;

/**
 * Handles the creation for table `act`.
 */
class m160815_162057_create_act_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%act}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'partner_id' => 'INT(10) UNSIGNED NULL',
            'client_id' => 'INT(10) UNSIGNED NULL',
            'type_id' => 'INT(10) UNSIGNED NULL',
            'card_id' => 'INT(10) UNSIGNED NULL',
            'mark_id' => 'INT(10) UNSIGNED NULL',
            'expense' => 'INT(10) NOT NULL DEFAULT \'0\'',
            'income' => 'INT(10) NOT NULL DEFAULT \'0\'',
            'profit' => 'INT(10) NOT NULL DEFAULT \'0\'',
            'service_type' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
            'status' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'number' => 'VARCHAR(45) NOT NULL',
            'extra_number' => 'VARCHAR(45) NOT NULL',
            'check' => 'VARCHAR(45) NOT NULL',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
            'served_at' => 'INT(11) NOT NULL',
        ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%act}}');
        $this->execute('SET foreign_key_checks = 1');
    }
}
