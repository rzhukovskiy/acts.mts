<?php

use yii\db\Migration;

class m161108_154030_add_act_id_field_to_monthly_act_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%monthly_act}}', 'act_id', $this->integer()->unsigned()->null()->after('service_id'));
        $this->createIndex('act_id', '{{%monthly_act}}', 'act_id');
        $this->addForeignKey('monthly_act_act_id',
            '{{%monthly_act}}',
            'act_id',
            '{{%act}}',
            'id',
            'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('monthly_act_act_id', '{{%monthly_act}}');
        $this->dropColumn('{{%monthly_act}}', 'act_id');
    }
}
