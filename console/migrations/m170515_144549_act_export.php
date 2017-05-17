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
            'period' => $this->string(7)->append('CHARACTER SET utf8 COLLATE utf8_unicode_ci'),
            'name' => $this->string(255)->append('CHARACTER SET utf8 COLLATE utf8_unicode_ci'),
            'data_load' => $this->string(30)->append('CHARACTER SET utf8 COLLATE utf8_unicode_ci')
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
