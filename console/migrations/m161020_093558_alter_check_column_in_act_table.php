<?php

use yii\db\Migration;

class m161020_093558_alter_check_column_in_act_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%act}}', 'check', $this->string(45)->null());
    }

    public function down()
    {
        $this->alterColumn('{{%act}}', 'check', $this->string(45)->notNull());
    }
}
