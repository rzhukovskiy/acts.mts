<?php

use yii\db\Migration;

class m180214_090236_company_info_edo extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}','edo', 'tinyint(1) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}','edo');
    }
}
