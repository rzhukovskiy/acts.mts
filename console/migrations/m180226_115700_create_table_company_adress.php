<?php

use yii\db\Migration;

class m180226_115700_create_table_company_adress extends Migration
{
    public function up()
    {
        $this->createTable('{{%company_address}}', [
            'id' => $this->primaryKey(),
            'company_id' => 'int(11) NOT NULL',
            'type' => 'tinyint(1) DEFAULT 0',
            'address' => 'varchar(255) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%company_address}}');
    }
}
