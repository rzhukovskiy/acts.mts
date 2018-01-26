<?php

use yii\db\Migration;

class m180125_144222_create_table_entry_event extends Migration
{
    public function up()
    {
        $this->createTable('{{%entry_event}}', [
            'id' => $this->primaryKey(),
            'company_id' => 'int(11) NOT NULL',
            'comment' => 'text DEFAULT NULL',
            'date_from' => 'varchar(20) DEFAULT NULL',
            'date_to' => 'varchar(20) DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%entry_event}}');
    }
}