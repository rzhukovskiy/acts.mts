<?php

use yii\db\Migration;

/**
 * Handles the creation of table `plan`.
 */
class m161125_080714_create_plan_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%plan}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'task_name' => $this->string(1000)->notNull(),
            'status' => $this->smallInteger()->notNull(),
            'comment' =>$this->text(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%plan}}');
    }
}
