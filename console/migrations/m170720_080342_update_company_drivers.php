<?php

use yii\db\Migration;

class m170720_080342_update_company_drivers extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%company_driver}}', 'mark_id');
        $this->dropColumn('{{%company_driver}}', 'type_id');
        $this->addColumn('{{%company_driver}}', 'car_id', "integer(11) DEFAULT 0 NOT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%company_driver}}', 'car_id');
        $this->addColumn('{{%company_driver}}', 'mark_id', "integer(11)");
        $this->addColumn('{{%company_driver}}', 'type_id', "integer(11)");
    }
}
