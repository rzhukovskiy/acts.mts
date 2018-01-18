<?php

use yii\db\Migration;

class m180116_144649_create_task_user extends Migration
{
    public function up()
    {
        $this->createTable('{{%task_user}}', [
            'id' => $this->primaryKey(),
            'task' => 'text NOT NULL',
            'for_user' => 'int(11) NOT NULL',
            'from_user' => 'int(11) NOT NULL',
            'data_status' => 'varchar(20) DEFAULT NULL',
            'data' => 'varchar(20) DEFAULT NULL',
            'status' => 'smallint(6) DEFAULT 0',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%task_user}}');
    }
}

