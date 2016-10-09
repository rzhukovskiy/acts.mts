<?php

use yii\db\Migration;

/**
 * Handles dropping phone from table `company`.
 */
class m161009_140221_drop_phone_column_from_company_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%company}}', 'phone');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn('{{%company}}', 'phone', $this->string(255));
    }
}
