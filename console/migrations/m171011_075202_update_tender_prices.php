<?php

use yii\db\Migration;

class m171011_075202_update_tender_prices extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%tender}}','price_nds', 'DECIMAL(12,2) NOT NULL');
        $this->alterColumn('{{%tender}}','pre_income', 'DECIMAL(12,2) NOT NULL');
        $this->alterColumn('{{%tender}}','first_price', 'DECIMAL(12,2) NOT NULL');
        $this->alterColumn('{{%tender}}','final_price', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','contract_security', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','participate_price', 'DECIMAL(12,2) DEFAULT NULL');

        $this->alterColumn('{{%tender}}','date_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','term_contract', 'varchar(20) DEFAULT NULL');
    }

    public function down()
    {
        $this->alterColumn('{{%tender}}','price_nds', 'int(10) NOT NULL');
        $this->alterColumn('{{%tender}}','pre_income', 'int(10) NOT NULL');
        $this->alterColumn('{{%tender}}','first_price', 'int(10) NOT NULL');
        $this->alterColumn('{{%tender}}','final_price', 'int(10) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','contract_security', 'int(10) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','participate_price', 'int(10) DEFAULT NULL');

        $this->alterColumn('{{%tender}}','date_contract', 'varchar(20) NOT NULL');
        $this->alterColumn('{{%tender}}','term_contract', 'varchar(20) NOT NULL');
    }
}
