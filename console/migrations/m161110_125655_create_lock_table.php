<?php

use yii\db\Migration;

/**
 * Handles the creation of table `lock`.
 */
class m161110_125655_create_lock_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%lock}}', [
            'id' => $this->primaryKey(),
            'type' => $this->integer()->notNull(),
            'period' => $this->string()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%lock}}');
    }
}
