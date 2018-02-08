<?php

use yii\db\Migration;

class m180208_075415_act_client_type extends Migration
{
    public function up()
    {
        $this->addColumn('{{%act}}','type_client', 'int(10) DEFAULT 0 NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%act}}','type_client');
    }
}
