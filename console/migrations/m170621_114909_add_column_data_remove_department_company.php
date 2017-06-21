<?php

use yii\db\Migration;

class m170621_114909_add_column_data_remove_department_company extends Migration
{
    public function up()
    {
        $this->addColumn('{{%department_company}}', 'remove_date', "varchar(20) DEFAULT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%department_company}}', 'remove_date');
    }

}
