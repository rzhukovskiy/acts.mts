<?php

use yii\db\Migration;

class m170321_132601_new_row_table extends Migration
{
    public function up()
    {

        $this->addColumn('lock', 'company_id', $this->integer(10) . "DEFAULT 0");

    }

    public function down()
    {
        $this->dropColumn('lock', 'company_id');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
