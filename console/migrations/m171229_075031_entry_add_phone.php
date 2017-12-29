<?php

use yii\db\Migration;

class m171229_075031_entry_add_phone extends Migration
{
    public function up()
    {
        $this->addColumn('{{%entry}}','phone', 'varchar(255)');

    }

    public function down()
    {
        $this->dropColumn('{{%entry}}','phone');
    }
}
