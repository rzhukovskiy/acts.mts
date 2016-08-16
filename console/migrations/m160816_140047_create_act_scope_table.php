<?php

use yii\db\Migration;

/**
 * Handles the creation for table `act_scope`.
 */
class m160816_140047_create_act_scope_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%act_scope}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'act_id' => 'INT(10) UNSIGNED NULL',
            'company_id' => 'INT(10) UNSIGNED NULL',
            'company_service_id' => 'INT(10) UNSIGNED NULL',
            'amount' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'price' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'description' => 'VARCHAR(255) NOT NULL',
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
        $this->dropTable('{{%act_scope}}');
        $this->execute('SET foreign_key_checks = 1');
    }
}
