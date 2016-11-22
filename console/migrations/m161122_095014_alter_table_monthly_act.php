<?php

use yii\db\Migration;

class m161122_095014_alter_table_monthly_act extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%monthly_act}}', 'profit');
    }

    public function down()
    {
        $this->addColumn('{{%monthly_act}}', 'profit', $this->integer()->notNull());
    }

}
