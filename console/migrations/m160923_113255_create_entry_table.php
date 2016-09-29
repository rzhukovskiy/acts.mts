<?php

use yii\db\Migration;

/**
 * Handles the creation for table `entry`.
 */
class m160923_113255_create_entry_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%entry}}', [
            'id' => $this->primaryKey(),
            'company_id' => 'INT(10) UNSIGNED NULL',
            'type_id' => 'INT(10) UNSIGNED NULL',
            'card_id' => 'INT(10) UNSIGNED NULL',
            'mark_id' => 'INT(10) UNSIGNED NULL',
            'service_type' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
            'status' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'number' => 'VARCHAR(45) NOT NULL',
            'extra_number' => 'VARCHAR(45) NOT NULL',
            'created_at' => 'INT(11) NOT NULL',
            'updated_at' => 'INT(11) NOT NULL',
            'start_at' => 'INT(11) NOT NULL',
            'end_at' => 'INT(11) NOT NULL',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%entry}}');
    }
}
