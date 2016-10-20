<?php

use yii\db\Migration;

/**
 * Handles adding is_partner to table `monthly_act`.
 */
class m161019_184832_add_is_partner_column_to_monthly_act_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%monthly_act}}', 'is_partner', $this->boolean()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%monthly_act}}', 'is_partner');
    }
}
