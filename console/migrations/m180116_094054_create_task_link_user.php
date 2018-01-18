<?php

use yii\db\Migration;

class m180116_094054_create_task_link_user extends Migration
{
    public function up()
    {
        $this->createTable('{{%task_user_link}}', [
            'id' => $this->primaryKey(),
            'task_id' => 'int(11) NOT NULL',
            'for_user_copy' => 'int(11) DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%task_user_link}}');
    }
}
