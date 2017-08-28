<?php

use yii\db\Migration;

class m170828_084041_company_info_nds_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}', 'nds', "tinyint(1) DEFAULT 0 NOT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}', 'nds');
    }

}
