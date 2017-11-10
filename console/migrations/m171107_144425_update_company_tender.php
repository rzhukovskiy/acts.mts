<?php

use yii\db\Migration;

class m171107_144425_update_company_tender extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%tender}}','first_price');
        $this->dropColumn('{{%tender}}','participate_price');
        $this->addColumn('{{%tender}}', 'purchase_status', 'smallint(3) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'comment_status_proc', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'user_id', 'varchar(200) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'comment_customer', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'inn_customer', 'varchar(200) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'contacts_resp_customer', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'maximum_purchase_price', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'cost_purchase_completion', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'maximum_purchase_nds', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'maximum_purchase_notnds', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'maximum_agreed_calcnds', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'maximum_agreed_calcnotnds', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'site_fee_participation', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'ensuring_application', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'inn_competitors', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'comment_date_contract', 'text DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_search', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','city', 'varchar(255) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','place', 'varchar(255) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','number_purchase', 'varchar(255) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','customer', 'varchar(255) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','service_type', 'varchar(200) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','price_nds', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','pre_income', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','final_price', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','percent_down', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','percent_max', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','federal_law', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','method_purchase', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','contract_security', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_request_security', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_status_request', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_contract_security', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_status_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','notice_eis', 'varchar(100) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','key_type', 'tinyint(1) DEFAULT 0');
        $this->alterColumn('{{%tender}}','competitor', 'varchar(255) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_request_start', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_request_end', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','time_request_process', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','time_bidding_start', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','time_bidding_end', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','term_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','comment', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}', 'tender_close', 'tinyint(1) DEFAULT 0');
    }

    public function down()
    {
        //$this->addColumn('{{%tender}}','first_price', 'DECIMAL(12,2) DEFAULT NULL');
        //$this->addColumn('{{%tender}}','participate_price', 'DECIMAL(12,2) DEFAULT NULL');
        $this->dropColumn('{{%tender}}', 'purchase_status');
        $this->dropColumn('{{%tender}}', 'comment_status_proc');
        $this->dropColumn('{{%tender}}', 'user_id');
        $this->dropColumn('{{%tender}}', 'comment_customer');
        $this->dropColumn('{{%tender}}', 'inn_customer');
        $this->dropColumn('{{%tender}}', 'contacts_resp_customer');
        $this->dropColumn('{{%tender}}', 'maximum_purchase_price');
        $this->dropColumn('{{%tender}}', 'cost_purchase_completion');
        $this->dropColumn('{{%tender}}', 'maximum_purchase_nds');
        $this->dropColumn('{{%tender}}', 'maximum_purchase_notnds');
        $this->dropColumn('{{%tender}}', 'maximum_agreed_calcnds');
        $this->dropColumn('{{%tender}}', 'maximum_agreed_calcnotnds');
        $this->dropColumn('{{%tender}}', 'site_fee_participation');
        $this->dropColumn('{{%tender}}', 'ensuring_application');
        $this->dropColumn('{{%tender}}', 'inn_competitors');
        $this->dropColumn('{{%tender}}', 'comment_date_contract');
        $this->alterColumn('{{%tender}}','date_search', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','city', 'varchar(255) NOT NULL');
        $this->alterColumn('{{%tender}}','place', 'varchar(255) NOT NULL');
        $this->alterColumn('{{%tender}}','number_purchase', 'varchar(255) NOT NULL');
        $this->alterColumn('{{%tender}}','customer', 'varchar(255) NOT NULL');
        $this->alterColumn('{{%tender}}','service_type', 'varchar(200) NOT NULL');
        $this->alterColumn('{{%tender}}','price_nds', 'DECIMAL(12,2) NOT NULL');
        $this->alterColumn('{{%tender}}','pre_income', 'DECIMAL(12,2) NOT NULL');
        $this->alterColumn('{{%tender}}','final_price', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','percent_down', 'smallint(3) NOT NULL');
        $this->alterColumn('{{%tender}}','percent_max', 'smallint(3) NOT NULL');
        $this->alterColumn('{{%tender}}','federal_law', 'smallint(3) NOT NULL');
        $this->alterColumn('{{%tender}}','method_purchase', 'smallint(3) NOT NULL');
        $this->alterColumn('{{%tender}}','contract_security', 'DECIMAL(12,2) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_request_security', 'smallint(3) NOT NULL');
        $this->alterColumn('{{%tender}}','date_status_request', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_contract_security', 'smallint(3) NOT NULL');
        $this->alterColumn('{{%tender}}','date_status_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','notice_eis', 'varchar(100) NOT NULL');
        $this->alterColumn('{{%tender}}','key_type', 'tinyint(1) NOT NULL DEFAULT 0');
        $this->alterColumn('{{%tender}}','competitor', 'varchar(255) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','date_request_start', 'varchar(20) NOT NULL');
        $this->alterColumn('{{%tender}}','date_request_end', 'varchar(20) NOT NULL');
        $this->alterColumn('{{%tender}}','time_request_process', 'varchar(20) NOT NULL');
        $this->alterColumn('{{%tender}}','time_bidding_start', 'varchar(20) NOT NULL');
        $this->alterColumn('{{%tender}}','time_bidding_end', 'varchar(20) NOT NULL');
        $this->alterColumn('{{%tender}}','date_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','term_contract', 'varchar(20) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','comment', 'text DEFAULT NULL');
        //$this->dropColumn('{{%tender}}', 'tender_close');

    }
    

}
