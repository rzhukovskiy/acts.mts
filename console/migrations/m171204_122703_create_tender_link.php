<?php

use yii\db\Migration;

class m171204_122703_create_tender_link extends Migration
{
    public function up()
    {
        $this->createTable('{{%tender_links}}', [
            'id' => $this->primaryKey(),
            'tender_id' => 'int(11) NOT NULL',
            'member_id' => 'int(11) NOT NULL',
            'winner' => 'tinyint(1) DEFAULT 0',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%tender_links}}');
    }

}
