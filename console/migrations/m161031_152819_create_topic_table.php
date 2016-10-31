<?php

use yii\db\Migration;

/**
 * Handles the creation for table `topic`.
 */
class m161031_152819_create_topic_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%topic}}', [
            'id' => $this->primaryKey(),
            'topic' => $this->string(255),
            'from' => $this->integer()->unsigned()->notNull(),
            'to' => $this->integer()->unsigned()->notNull(),
            'message_id' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%topic}}');
    }
}
