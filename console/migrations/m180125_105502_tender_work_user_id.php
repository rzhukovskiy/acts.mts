<?php

use yii\db\Migration;

class m180125_105502_tender_work_user_id extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%tender}}','work_user_id');
        $this->dropColumn('{{%tender}}','work_user_time');
    }

    public function down()
    {

    }
}
