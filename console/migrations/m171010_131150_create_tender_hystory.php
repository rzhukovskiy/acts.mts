<?php

use yii\db\Migration;

class m171010_131150_create_tender_hystory extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender_hystory}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11),
            'user_id' => $this->integer(11),
            'remove_date' => 'varchar(20) DEFAULT NULL',
            'remove_id' => 'int(11) DEFAULT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%tender_hystory}}');
    }
}
