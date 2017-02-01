<?php

use yii\db\Migration;

/**
 * Handles adding is_lost to table `card`.
 */
class m170201_083156_add_is_lost_column_to_card_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%card}}', 'is_lost', $this->integer()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%card}}', 'is_lost');
    }
}
