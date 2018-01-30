<?php

use yii\db\Migration;

class m180126_114658_tender_in_task_user extends Migration
{
    public function up()
    {
        $this->addColumn('{{%task_user}}','tender_id', 'int(11) DEFAULT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%task_user}}','tender_id');
    }
}
