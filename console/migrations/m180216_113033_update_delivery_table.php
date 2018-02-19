<?php

use yii\db\Migration;

class m180216_113033_update_delivery_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%delivery}}','city', 'varchar(255)');
    }

    public function down()
    {
        $this->dropColumn('{{%delivery}}','city');
    }
}