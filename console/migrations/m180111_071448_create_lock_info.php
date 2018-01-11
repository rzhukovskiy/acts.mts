<?php

use yii\db\Migration;

class m180111_071448_create_lock_info extends Migration
{
    public function up()
    {
        $this->createTable('{{%lock_info}}', [
            'id' => $this->primaryKey(),
            'partner_id' => 'int(11) NOT NULL',
            'type' => 'int(11) NOT NULL',
            'period' => 'varchar(255) NOT NULL',
            'comment' => 'varchar(255) DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%lock_info}}');
    }
}