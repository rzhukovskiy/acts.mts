<?php

use yii\db\Migration;

class m170619_135120_new_table_email extends Migration
{

    public function up()
    {
        $this->createTable('{{%email}}', [
            'id' => $this->primaryKey(),
            'name' => "varchar(255) NOT NULL",
            'type' => "tinyint(1) NOT NULL DEFAULT 0",
            'title' => "varchar(255) NOT NULL",
            'text' => 'text NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%email}}');
    }

}