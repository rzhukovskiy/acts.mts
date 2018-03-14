<?php

use yii\db\Migration;

class m180314_104712_update_table_company_info extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}','payment_period', 'varchar(20)');
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}','payment_period');
    }
}
