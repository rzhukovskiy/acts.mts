<?php

use yii\db\Migration;

/**
 * Handles adding old_id to table `company`.
 */
class m161029_200607_add_old_id_column_to_company_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company}}', 'old_id', $this->integer()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company}}', 'old_id');
    }
}
