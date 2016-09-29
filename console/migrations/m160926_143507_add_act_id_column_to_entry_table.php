<?php

use yii\db\Migration;

/**
 * Handles adding act_id to table `entry`.
 */
class m160926_143507_add_act_id_column_to_entry_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%entry}}', 'act_id', $this->integer()->unsigned()->defaultValue(null));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%entry}}', 'act_id');
    }
}
