<?php

use yii\db\Migration;

class m180205_133231_update_table_tender_owner extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender_owner}}','date_bidding', 'varchar(20)');
        $this->addColumn('{{%tender_owner}}','date_consideration', 'varchar(20)');
        $this->addColumn('{{%tender_owner}}','purchase_name', 'text');
        $this->addColumn('{{%tender_owner}}','fz', 'varchar(255)');
        $this->addColumn('{{%tender_owner}}','customer', 'varchar(255)');
        $this->addColumn('{{%tender_owner}}','customer_full', 'text');
        $this->addColumn('{{%tender_owner}}','inn_customer', 'varchar(20)');
        $this->addColumn('{{%tender_owner}}','link_official', 'varchar(255)');
        $this->addColumn('{{%tender_owner}}','request_security', 'decimal(12,2)');
        $this->addColumn('{{%tender_owner}}','electronic_platform', 'varchar(255)');
        $this->alterColumn('{{%tender_owner}}','text', 'text DEFAULT NULL');
        $this->addColumn('{{%tender_owner}}','status', 'smallint(3) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%tender_owner}}','status');
        $this->alterColumn('{{%tender_owner}}','text', 'text NOT NULL');
        $this->dropColumn('{{%tender_owner}}','date_bidding');
        $this->dropColumn('{{%tender_owner}}','date_consideration');
        $this->dropColumn('{{%tender_owner}}','purchase_name');
        $this->dropColumn('{{%tender_owner}}','fz');
        $this->dropColumn('{{%tender_owner}}','customer');
        $this->dropColumn('{{%tender_owner}}','customer_full');
        $this->dropColumn('{{%tender_owner}}','inn_customer');
        $this->dropColumn('{{%tender_owner}}','link_official');
        $this->dropColumn('{{%tender_owner}}','request_security');
        $this->dropColumn('{{%tender_owner}}','electronic_platform');
    }
}