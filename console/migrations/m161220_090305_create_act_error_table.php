<?php

use yii\db\Migration;

/**
 * Handles the creation of table `act_error`.
 */
class m161220_090305_create_act_error_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%act_error}}', [
            'id' => $this->primaryKey(),
            'act_id' => $this->integer()->unsigned()->notNull(),
            'error_type' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%act_error}}');
    }
}
