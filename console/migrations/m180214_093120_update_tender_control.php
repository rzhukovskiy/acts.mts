<?php

use yii\db\Migration;

class m180214_093120_update_tender_control extends Migration
{
    public function up()
    {
        $this->renameColumn('{{%tender_control}}','return', 'tender_return');
    }

    public function down()
    {
        $this->renameColumn('{{%tender_control}}','tender_return', 'return');
    }
}
