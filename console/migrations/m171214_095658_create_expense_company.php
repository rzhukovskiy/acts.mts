<?php

use yii\db\Migration;

class m171214_095658_create_expense_company extends Migration
{
    public function up()
    {
        $this->createTable('{{%expense_company}}', [
            'id' => $this->primaryKey(),
            'type' => 'int(11) NOT NULL',
            'name' => 'varchar(255) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%expense_company}}');
    }
}
