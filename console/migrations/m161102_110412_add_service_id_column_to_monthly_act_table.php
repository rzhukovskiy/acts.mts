<?php

use yii\db\Migration;

/**
 * Handles adding service_id to table `monthly_act`.
 */
class m161102_110412_add_service_id_column_to_monthly_act_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%monthly_act}}', 'service_id', $this->integer()->unsigned()->null()->after('type_id'));
        $this->createIndex('service_id', '{{%monthly_act}}', 'service_id');
        $this->addForeignKey('monthly_act_service_id',
            '{{%monthly_act}}',
            'service_id',
            '{{%service}}',
            'id',
            'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('monthly_act_service_id', '{{%monthly_act}}');
        $this->dropColumn('{{%monthly_act}}', 'service_id');
    }
}
