<?php

use yii\db\Migration;

class m180220_104938_update_company_info extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}','count_checks', 'int(11)');
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}','count_checks');
    }
}