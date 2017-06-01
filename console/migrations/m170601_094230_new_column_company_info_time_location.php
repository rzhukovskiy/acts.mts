<?php

use yii\db\Migration;

class m170601_094230_new_column_company_info_time_location extends Migration
{
    public function up()
    {

        $this->addColumn('{{%company_info}}', 'time_location', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColumn('{{%company_info}}', 'website', "varchar(150) DEFAULT NULL");

    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}', 'time_location');
        $this->dropColumn('{{%company_info}}', 'website');
    }
}
