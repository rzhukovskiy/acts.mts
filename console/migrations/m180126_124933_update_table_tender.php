<?php

use yii\db\Migration;

class m180126_124933_update_table_tender extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%tender}}','date_search');
        $this->dropColumn('{{%tender}}','site_fee_participation');
        $this->dropColumn('{{%tender}}','ensuring_application');
        $this->dropColumn('{{%tender}}','contract_security');
        $this->dropColumn('{{%tender}}','key_type');
        $this->dropColumn('{{%tender}}','comment_status_proc');
        $this->dropColumn('{{%tender}}','notice_eis');
        $this->dropColumn('{{%tender}}','maximum_purchase_price');
        $this->dropColumn('{{%tender}}','cost_purchase_completion');
        $this->dropColumn('{{%tender}}','pre_income');
        $this->dropColumn('{{%tender}}','last_sentence_nds');
        $this->dropColumn('{{%tender}}','last_sentence_nonds');
        $this->dropColumn('{{%tender}}','percent_down');
        $this->dropColumn('{{%tender}}','maximum_purchase_nds');
        $this->dropColumn('{{%tender}}','maximum_purchase_notnds');
        $this->dropColumn('{{%tender}}','percent_max');
        $this->dropColumn('{{%tender}}','maximum_agreed_calcnds');
        $this->dropColumn('{{%tender}}','maximum_agreed_calcnotnds');
        $this->dropColumn('{{%tender_control}}','eis_platform');
        $this->addColumn('{{%tender}}','site_address', 'int(11) DEFAULT NULL');
        $this->addColumn('{{%tender}}','purchase', 'varchar(255) DEFAULT NULL');
        $this->addColumn('{{%tender_control}}','tender_id', 'int(11)');
    }

    public function down()
    {
        $this->addColumn('{{%tender}}','date_search', 'varchar(20) DEFAULT NULL');
        $this->addColumn('{{%tender}}','site_fee_participation', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','ensuring_application', '	decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','contract_security', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','key_type', 'varchar(200) DEFAULT NULL');
        $this->addColumn('{{%tender}}','comment_status_proc', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}','notice_eis', 'varchar(100) DEFAULT NULL');
        $this->addColumn('{{%tender}}','maximum_purchase_price', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','cost_purchase_completion', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','pre_income', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','last_sentence_nds', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','last_sentence_nonds', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','percent_down', 'smallint(3) DEFAULT NULL');
        $this->addColumn('{{%tender}}','maximum_purchase_nds', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','maximum_purchase_notnds', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','percent_max', 'smallint(3) DEFAULT NULL');
        $this->addColumn('{{%tender}}','maximum_agreed_calcnds', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','maximum_agreed_calcnotnds', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender_control}}','eis_platform', 'varchar(255) DEFAULT NULL');
        $this->dropColumn('{{%tender}}','site_address');
        $this->dropColumn('{{%tender}}','purchase');
        $this->dropColumn('{{%tender_control}}','tender_id');

    }
}