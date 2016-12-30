<?php

use yii\db\Migration;

/**
 * Handles adding user_id to table `entry`.
 */
class m161230_094225_add_user_id_column_to_entry_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%entry}}', 'user_id', $this->integer()->unsigned());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%entry}}', 'user_id');
    }
}
