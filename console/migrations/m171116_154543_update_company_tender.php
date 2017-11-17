<?php

use yii\db\Migration;

class m171116_154543_update_company_tender extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%tender}}','method_purchase', 'varchar(200) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','key_type', 'varchar(200) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','federal_law', 'varchar(200) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_request_security', 'varchar(200) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_contract_security', 'varchar(200) DEFAULT NULL');
    }

    public function down()
    {
        $this->alterColumn('{{%tender}}','method_purchase', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','key_type', 'tinyint(1) DEFAULT 0');
        $this->alterColumn('{{%tender}}','federal_law', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_request_security', 'smallint(3) DEFAULT NULL');
        $this->alterColumn('{{%tender}}','status_contract_security', 'smallint(3) DEFAULT NULL');
    }

}
