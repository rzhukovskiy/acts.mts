<?php

use yii\db\Migration;

/**
 * Handles adding user_id to table `company_offer`.
 */
class m161023_170455_add_user_id_column_to_company_offer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company_offer}}', 'user_id', $this->integer()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company_offer}}', 'user_id');
    }
}
