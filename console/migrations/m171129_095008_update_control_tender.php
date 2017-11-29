<?php

use yii\db\Migration;

class m171129_095008_update_control_tender extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender_control}}','payment_status', 'smallint(6) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%tender_control}}','payment_status');
    }

}
