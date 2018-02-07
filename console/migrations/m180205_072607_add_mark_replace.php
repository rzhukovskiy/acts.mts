<?php

use yii\db\Migration;

class m180205_072607_add_mark_replace extends Migration
{
    public function up()
    {
        $this->addColumn('{{%service_replace}}','mark_client', 'smallint(6) NOT NULL DEFAULT 0');
        $this->addColumn('{{%service_replace}}','mark_partner', 'smallint(6) NOT NULL DEFAULT 0');
        $this->addColumn('{{%service_replace_item}}','car_mark', 'smallint(6) NOT NULL DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%service_replace}}','mark_client');
        $this->dropColumn('{{%service_replace}}','mark_partner');
        $this->dropColumn('{{%service_replace_item}}','car_mark');

    }
}
