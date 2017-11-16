<?php

use yii\db\Migration;

class m171115_073103_create_tender_lists extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender_lists}}', [
            'id' => $this->primaryKey(),
            'description' => 'VARCHAR(255) NOT NULL',
            'required' => 'TINYINT(1) DEFAULT 0',
            'type' => 'TINYINT(1) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%tender_lists}}');
    }
}
