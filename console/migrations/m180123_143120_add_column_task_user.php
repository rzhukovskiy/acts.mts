<?php

use yii\db\Migration;

class m180123_143120_add_column_task_user extends Migration
{
    public function up()
    {
        $this->addColumn('{{%task_user}}','title', 'varchar(255)');

    }

    public function down()
    {
        $this->dropColumn('{{%task_user}}','title');
    }

}
