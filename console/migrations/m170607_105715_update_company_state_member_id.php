<?php

use yii\db\Migration;

class m170607_105715_update_company_state_member_id extends Migration
{

    public function up()
    {
        $this->alterColumn('{{%company_state}}', 'member_id', 'varchar(200) NOT NULL', 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->alterColumn('{{%company_state}}' ,'member_id', 'int(11) NOT NULL');
    }
}
