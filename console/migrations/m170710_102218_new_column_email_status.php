<?php

use yii\db\Migration;

class m170710_102218_new_column_email_status extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_offer}}', 'email_status', "tinyint(1) DEFAULT 1 NOT NULL");
    }

    public function down()
    {
        $this->dropColumn('{{%company_offer}}', 'email_status');
    }
}
