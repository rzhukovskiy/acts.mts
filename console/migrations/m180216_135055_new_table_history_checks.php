<?php

use yii\db\Migration;

class m180216_135055_new_table_history_checks extends Migration
{
    public function up()
    {
        $this->createTable('{{%history_checks}}', [
            'id' => $this->primaryKey(),
            'company_id' => 'int(11) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'serial_number' => 'varchar(70) NOT NULL',
            'date_send' => 'varchar(20) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%history_checks}}');
    }
}
