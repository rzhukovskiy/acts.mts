<?php

use yii\db\Migration;

/**
 * Handles adding name to table `company_member`.
 */
class m161027_141050_add_name_column_to_company_member_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company_member}}', 'name', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company_member}}', 'name');
    }
}
