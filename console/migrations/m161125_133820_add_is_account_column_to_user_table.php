<?php

use yii\db\Migration;

/**
 * Handles adding is_account to table `user`.
 */
class m161125_133820_add_is_account_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%user}}', 'is_account', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user}}', 'is_account');
    }
}
