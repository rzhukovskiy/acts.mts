<?php

use yii\db\Migration;

class m171205_093539_company_info_location extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}', 'lat', 'varchar(20) DEFAULT NULL');
        $this->addColumn('{{%company_info}}', 'lng', 'varchar(20) DEFAULT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}','lat');
        $this->dropColumn('{{%company_info}}','lng');
    }
}
