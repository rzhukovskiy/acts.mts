<?php

use yii\db\Migration;

class m171201_125421_create_tender_member extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender_members}}', [
            'id' => $this->primaryKey(),
            'company_name' => 'varchar(255) DEFAULT NULL',
            'inn' => 'varchar(30) DEFAULT NULL',
            'city' => 'varchar(255) DEFAULT NULL',
            'comment' => 'text DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%tender_members}}');
    }

}
