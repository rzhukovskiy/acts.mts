<?php

use yii\db\Migration;

class m171005_080623_monthly_act_predoplata extends Migration
{
    public function up()
    {
        $this->addColumn('{{%monthly_act}}', 'prepayment', "int(10) DEFAULT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%monthly_act}}', 'prepayment');
    }
}
