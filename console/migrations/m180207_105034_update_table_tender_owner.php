<?php

use yii\db\Migration;

class m180207_105034_update_table_tender_owner extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender_owner}}','number', 'varchar(30)');
        $this->addColumn('{{%tender}}','customer_full', 'text');
        $this->addColumn('{{%tender}}','link', 'varchar(255)');
        $this->addColumn('{{%tender}}','request_security', 'decimal(12,2)');
        $this->addColumn('{{%tender}}','contract_security', 'decimal(12,2)');
        $this->alterColumn('{{%tender}}','purchase', 'text');
    }

    public function down()
    {
        $this->dropColumn('{{%tender_owner}}','number');
        $this->dropColumn('{{%tender}}','customer_full');
        $this->dropColumn('{{%tender}}','link');
        $this->dropColumn('{{%tender}}','request_security');
        $this->dropColumn('{{%tender}}','contract_security');
        $this->alterColumn('{{%tender}}','purchase', 'varchar(255)');
    }
}
