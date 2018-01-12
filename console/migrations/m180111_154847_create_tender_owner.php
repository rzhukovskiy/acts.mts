<?php

use yii\db\Migration;

class m180111_154847_create_tender_owner extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender_owner}}', [
            'id' => $this->primaryKey(),
            'text' => 'text NOT NULL',
            'tender_user' => 'int(11) DEFAULT 0',
            'tender_id' => 'int(11) DEFAULT NULL',
            'data' => 'varchar(20) DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%tender_owner}}');
    }
}
