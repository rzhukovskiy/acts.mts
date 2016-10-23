<?php

use yii\db\Migration;

/**
 * Handles adding index to table `company_info`.
 */
class m161023_104407_add_index_column_to_company_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company_info}}', 'index', $this->string(255));
        $this->addColumn('{{%company_info}}', 'city', $this->string(255));
        $this->addColumn('{{%company_info}}', 'street', $this->string(255));
        $this->addColumn('{{%company_info}}', 'house', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company_info}}', 'index');
        $this->dropColumn('{{%company_info}}', 'city');
        $this->dropColumn('{{%company_info}}', 'street');
        $this->dropColumn('{{%company_info}}', 'house');
    }
}
