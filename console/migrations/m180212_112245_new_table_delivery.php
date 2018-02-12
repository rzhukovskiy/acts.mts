<?php

use yii\db\Migration;

class m180212_112245_new_table_delivery extends Migration
{
    public function up()
    {
        $this->createTable('{{%delivery}}', [
            'id' => $this->primaryKey(),
            'wash_name' => 'varchar(255) NOT NULL',
            'date_send' => 'varchar(20) DEFAULT NULL',
            'size' => 'varchar(20) DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%delivery}}');
    }
}