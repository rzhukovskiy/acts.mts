<?php

use yii\db\Migration;

class m170707_152251_new_column_remode_id extends Migration
{
    public function up()
    {
        $this->addColumn('{{%department_company}}', 'remove_id', "integer(11) DEFAULT 0 NOT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%department_company}}', 'remove_id');
    }
}
