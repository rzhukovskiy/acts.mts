<?php

use yii\db\Migration;

class m170712_143240_new_column_sub_type_company extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company}}', 'sub_type', "smallint(3) DEFAULT 0 NOT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%company}}', 'sub_type');
    }
}
