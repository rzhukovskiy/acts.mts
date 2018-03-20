<?php

use yii\db\Migration;

class m180319_135507_new_table_informing extends Migration
{
    public function up()
    {
        $this->createTable('{{%informing}}', [
            'id' => $this->primaryKey(),
            'text' => 'text NOT NULL',
            'from_user' => 'int(11) NOT NULL',
            'date_create' => 'varchar(20) NOT NULL',
            'is_archive' => 'tinyint(1) DEFAULT 0',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%informing}}');
    }
}
