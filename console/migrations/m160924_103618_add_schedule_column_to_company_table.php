<?php

use yii\db\Migration;

/**
 * Handles adding schedule to table `company`.
 */
class m160924_103618_add_schedule_column_to_company_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company}}', 'schedule', $this->smallInteger(1)->notNull()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company}}', 'schedule');
    }
}
