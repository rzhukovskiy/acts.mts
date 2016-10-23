<?php

use yii\db\Migration;

/**
 * Handles dropping address from table `company_info`.
 */
class m161023_104008_drop_address_column_from_company_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%company_info}}', 'address');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn('{{%company_info}}', 'address', $this->string(255));
    }
}
