<?php

use yii\db\Migration;

class m180306_105837_update_table_tender_control extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender_control}}','requisite', 'varchar(255)');
    }

    public function down()
    {
        $this->dropColumn('{{%tender_control}}','requisite');
    }
}
