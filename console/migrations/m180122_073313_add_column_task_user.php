<?php

use yii\db\Migration;

class m180122_073313_add_column_task_user extends Migration
{
    public function up()
    {
        $this->addColumn('{{%task_user}}','comment', 'text');
        $this->addColumn('{{%tender_owner}}','reason_not_take', 'text');
        $this->addColumn('{{%task_user}}','is_archive', 'tinyint(1) DEFAULT 0');

    }

    public function down()
    {
        $this->dropColumn('{{%task_user}}','comment');
        $this->dropColumn('{{%tender_owner}}','reason_not_take');
        $this->dropColumn('{{%task_user}}','is_archive');
    }

}
