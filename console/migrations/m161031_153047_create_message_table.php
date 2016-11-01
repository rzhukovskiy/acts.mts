<?php

use yii\db\Migration;

/**
 * Handles the creation for table `message`.
 */
class m161031_153047_create_message_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
            'text' => $this->text(),
            'from' => $this->integer()->unsigned()->notNull(),
            'to' => $this->integer()->unsigned()->notNull(),
            'topic_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'is_read' => $this->smallInteger()->unsigned(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%message}}');
    }
}
