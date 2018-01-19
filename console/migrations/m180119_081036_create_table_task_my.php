<?php

use yii\db\Migration;

class m180119_081036_create_table_task_my extends Migration
{
    public function up()
    {
        $this->createTable('{{%task_my}}', [
            'id' => $this->primaryKey(),
            'task' => 'text NOT NULL',
            'from_user' => 'int(11) NOT NULL',
            'data_status' => 'varchar(20) DEFAULT NULL',
            'data' => 'varchar(20) DEFAULT NULL',
            'status' => 'smallint(6) DEFAULT 0',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%task_my}}');
    }
}
