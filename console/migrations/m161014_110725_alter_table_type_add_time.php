<?php

use yii\db\Migration;

class m161014_110725_alter_table_type_add_time extends Migration
{
    public function up()
    {
        $this->addColumn('{{%type}}', 'time', $this->integer()->unsigned()->defaultValue(60));

    }

    public function down()
    {
        $this->dropColumn('{{%type}}', 'time');
    }

}
