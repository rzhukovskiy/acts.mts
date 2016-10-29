<?php

use yii\db\Migration;

/**
 * Handles adding pay to table `company_info`.
 */
class m161029_161248_add_pay_column_to_company_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company_info}}', 'pay', $this->string(255));
        $this->addColumn('{{%company_info}}', 'contract', $this->string());
        $this->addColumn('{{%company_info}}', 'contract_date', $this->integer()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company_info}}', 'pay');
        $this->dropColumn('{{%company_info}}', 'contract');
        $this->dropColumn('{{%company_info}}', 'contract_date');
    }
}
