<?php

use yii\db\Migration;

class m161128_180915_add_is_nested_field_to_company_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company}}', 'is_nested', $this->smallInteger()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company}}', 'is_nested');
    }
}
