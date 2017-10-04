<?php

use yii\db\Migration;

class m171004_102639_company_car_type_work extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company}}', 'car_type', "smallint(3) DEFAULT 0 NOT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%company}}', 'car_type');
    }
}
