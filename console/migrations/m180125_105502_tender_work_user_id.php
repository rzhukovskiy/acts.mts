<?php

use yii\db\Migration;

class m180125_105502_tender_work_user_id extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender}}','work_user_id', 'int(11) DEFAULT 0 NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%tender}}','work_user_id');

    }
}
