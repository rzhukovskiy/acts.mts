<?php

use yii\db\Migration;

class m180319_142548_new_table_informing_users extends Migration
{
    public function up()
    {
        $this->createTable('{{%informing_users}}', [
            'id' => $this->primaryKey(),
            'informing_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'status' => 'tinyint(1) DEFAULT 0',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%informing_users}}');
    }
}
