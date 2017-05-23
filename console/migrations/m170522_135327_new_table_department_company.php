<?php

use yii\db\Migration;

class m170522_135327_new_table_department_company extends Migration
{
    public function up()
    {
        $this->createTable('{{%department_company}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11),
            'user_id' => $this->integer(11),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%department_company}}');
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
