<?php

use yii\db\Migration;

class m180129_092432_new_table_changes extends Migration
{
    public function up()
    {
        $this->createTable('{{%changes}}', [
            'id' => $this->primaryKey(),
            'type' => 'tinyint(2) NOT NULL',
            'sub_type' => 'tinyint(2) DEFAULT NULL',
            'user_id' => 'int(11) NOT NULL',
            'company_id' => 'int(11) DEFAULT NULL',
            'type_id' => 'int(11) DEFAULT NULL',
            'old_value' => 'varchar(255) NOT NULL',
            'new_value' => 'varchar(255) NOT NULL',
            'status' => 'tinyint(2) NOT NULL',
            'date' => 'varchar(20) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%changes}}');
    }
}
