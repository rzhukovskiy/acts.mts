<?php

use yii\db\Migration;

class m171117_121903_penalty_company extends Migration
{

    public function Up()
    {
        $this->addColumn('{{%company}}', 'use_penalty', 'TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function Down()
    {
        $this->dropColumn('{{%company}}','use_penalty');
    }

}
