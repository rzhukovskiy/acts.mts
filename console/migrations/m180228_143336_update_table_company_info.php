<?php

use yii\db\Migration;

class m180228_143336_update_table_company_info extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}','features_work', 'varchar(255)');
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}','features_work');
    }
}
