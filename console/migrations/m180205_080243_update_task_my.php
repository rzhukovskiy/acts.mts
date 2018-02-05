<?php

use yii\db\Migration;

class m180205_080243_update_task_my extends Migration
{
    public function up()
    {
        $this->addColumn('{{%task_my}}','title', 'varchar(255)');
    }

    public function down()
    {
        $this->dropColumn('{{%task_my}}','title');

    }
}