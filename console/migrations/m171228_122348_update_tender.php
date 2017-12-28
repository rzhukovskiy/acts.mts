<?php

use yii\db\Migration;

class m171228_122348_update_tender extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender}}','site', 'varchar(200)');

    }

    public function down()
    {
        $this->dropColumn('{{%tender}}','site');
    }

}
