<?php

use yii\db\Migration;

class m171101_121134_add_column_show_member extends Migration
{
    public function Up()
    {
        $this->addColumn('{{%company_member}}', 'show_member', 'TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function Down()
    {
        $this->dropColumn('{{%company_member}}','show_member');
    }

}
