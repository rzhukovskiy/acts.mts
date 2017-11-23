<?php

use yii\db\Migration;

class m171121_072305_create_control_tender extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender_control}}', [
            'id' => $this->primaryKey(),
            'user_id' => 'int(11) DEFAULT NULL',
            'send' => 'decimal(12,2) DEFAULT NULL',
            'date_send' => 'varchar(20) DEFAULT NULL',
            'date_enlistment' => 'varchar(20) DEFAULT NULL',
            'site_address' => 'int(11) DEFAULT NULL',
            'platform' => 'varchar(255) DEFAULT NULL',
            'customer' => 'varchar(255) DEFAULT NULL',
            'purchase' => 'varchar(255) DEFAULT NULL',
            'eis_platform' => 'varchar(255) DEFAULT NULL',
            'type_payment' => 'int(11) DEFAULT NULL',
            'money_unblocking' => 'varchar(20) DEFAULT NULL',
            'return' => 'decimal(12,2) DEFAULT NULL',
            'date_return' => 'varchar(20) DEFAULT NULL',
            'balance_work' => 'decimal(12,2) DEFAULT NULL',
            'comment' => 'text DEFAULT NULL',
            'is_archive' => 'tinyint(1) DEFAULT 0'
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%tender_control}}');
    }

}
