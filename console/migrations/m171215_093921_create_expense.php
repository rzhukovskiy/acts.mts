<?php

use yii\db\Migration;

class m171215_093921_create_expense extends Migration
{
    public function up()
    {
        $this->createTable('{{%expense}}', [
            'id' => $this->primaryKey(),
            'expense_company' => 'int(11) NOT NULL',
            'type' => 'int(11) NOT NULL',
            'description' => 'varchar(255) DEFAULT NULL',
            'date' => 'varchar(20) NOT NULL',
            'sum' => 'decimal(12,2) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%expense}}');
    }
}