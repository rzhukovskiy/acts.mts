<?php

use yii\db\Migration;

class m170515_144549_act_export extends Migration
{

    public function up()
    {
        $this->createTable('{{%act_export}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11),
            'type' => $this->integer(2),
            'company' => $this->integer(1),
            'period' => $this->string(7),
            'name' => $this->string(255),
            'data_load' => $this->string(30)
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%act_export}}');
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
