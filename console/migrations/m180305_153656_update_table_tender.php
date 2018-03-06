<?php

use yii\db\Migration;

class m180305_153656_update_table_tender extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%tender}}','place');
        $this->dropColumn('{{%tender}}','status_request_security');
        $this->dropColumn('{{%tender}}','date_status_request');
        $this->dropColumn('{{%tender}}','status_contract_security');
        $this->dropColumn('{{%tender}}','date_status_contract');
        $this->dropColumn('{{%tender_control}}','platform');

    }

    public function down()
    {
        $this->addColumn('{{%tender}}','place', 'varchar(255)');
        $this->addColumn('{{%tender}}','status_request_security', 'varchar(200)');
        $this->addColumn('{{%tender}}','date_status_request', 'varchar(20)');
        $this->addColumn('{{%tender}}','status_contract_security', 'varchar(200)');
        $this->addColumn('{{%tender}}','date_status_contract', 'varchar(20)');
        $this->addColumn('{{%tender_control}}','platform', 'varchar(255)');
    }
}
