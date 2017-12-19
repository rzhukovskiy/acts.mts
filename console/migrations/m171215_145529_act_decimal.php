<?php

use yii\db\Migration;

class m171215_145529_act_decimal extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%act}}','expense', 'DECIMAL(10,4) NOT NULL');
        $this->alterColumn('{{%act}}','income', 'DECIMAL(10,4) NOT NULL');
        $this->alterColumn('{{%act}}','profit', 'DECIMAL(10,4) NOT NULL');
        $this->alterColumn('{{%act_scope}}','price', 'DECIMAL(10,4) NOT NULL');
        $this->alterColumn('{{%company_service}}','price', 'DECIMAL(10,4) NOT NULL');
    }

    public function down()
    {
        $this->alterColumn('{{%act}}','expense', 'int(10) NOT NULL');
        $this->alterColumn('{{%act}}','income', 'int(10) NOT NULL');
        $this->alterColumn('{{%act}}','profit', 'int(10) NOT NULL');
        $this->alterColumn('{{%act_scope}}','price', 'int(10) NOT NULL');
        $this->alterColumn('{{%company_service}}','price', 'int(10) NOT NULL');
    }
}
