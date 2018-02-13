<?php

use yii\db\Migration;

class m180212_131735_new_department_linking extends Migration
{
    public function up()
    {
        $this->createTable('{{%department_linking}}', [
            'id' => $this->primaryKey(),
            'type' => 'tinyint(2) NOT NULL',
            'user_id' => 'int(11) NOT NULL',
            'company_id' => 'int(11) NOT NULL',
            'created_at' => 'int(11) NOT NULL',
            'updated_at' => 'int(11) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%department_linking}}');
    }
}
