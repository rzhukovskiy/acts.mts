<?php

use yii\db\Migration;

class m171005_112407_new_table_tender extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender}}', [
            'id' => $this->primaryKey(),
            'company_id' => 'int(11) NOT NULL',
            'date_search' => 'varchar(20) DEFAULT NULL',
            'city' => 'varchar(255) NOT NULL',
            'place' => 'varchar(255) NOT NULL',
            'number_purchase' => 'varchar(255) NOT NULL',
            'customer' => 'varchar(255) NOT NULL',
            'service_type' => 'varchar(200) NOT NULL',
            'price_nds' => 'int(10) NOT NULL',
            'pre_income' => 'int(10) NOT NULL',
            'first_price' => 'int(10) NOT NULL',
            'final_price' => 'int(10) DEFAULT NULL',
            'percent_down' => 'smallint(3) NOT NULL',
            'percent_max' => 'smallint(3) NOT NULL',
            'federal_law' => 'smallint(3) NOT NULL',
            'method_purchase' => 'smallint(3) NOT NULL',
            'contract_security' => 'int(10) DEFAULT NULL',
            'participate_price' => 'int(10) DEFAULT NULL',
            'status_request_security' => 'smallint(3) NOT NULL',
            'date_status_request' => 'varchar(20) DEFAULT NULL',
            'status_contract_security' => 'smallint(3) NOT NULL',
            'date_status_contract' => 'varchar(20) DEFAULT NULL',
            'notice_eis' => 'varchar(100) NOT NULL',
            'key_type' => 'tinyint(1) NOT NULL DEFAULT 0',
            'competitor' => 'varchar(255) DEFAULT NULL',
            'date_request_start' => 'varchar(20) NOT NULL',
            'date_request_end' => 'varchar(20) NOT NULL',
            'time_request_process' => 'varchar(20) NOT NULL',
            'time_bidding_start' => 'varchar(20) NOT NULL',
            'time_bidding_end' => 'varchar(20) NOT NULL',
            'date_contract' => 'varchar(20) NOT NULL',
            'term_contract' => 'varchar(20) NOT NULL',
            'comment' => 'text DEFAULT NULL',
            'created_at' => 'int(11) NOT NULL',
            'updated_at' => 'int(11) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%tender}}');
    }
}
