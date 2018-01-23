<?php

use yii\db\Migration;

class m180123_105644_add_column_task_user extends Migration
{
    public function up()
    {
        $this->addColumn('{{%task_user}}','priority', 'smallint(6) DEFAULT 0');
        $this->addColumn('{{%task_my}}','priority', 'smallint(6) DEFAULT 0');

    }

    public function down()
    {
        $this->dropColumn('{{%task_user}}','priority');
        $this->dropColumn('{{%task_my}}','priority');
    }

}