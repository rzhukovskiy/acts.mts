<?php

use yii\db\Migration;

/**
 * Handles adding type to table `contact`.
 */
class m161022_065920_add_type_column_to_contact_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%contact}}', 'type', $this->smallInteger()->notNull());
        $this->alterColumn('{{%contact}}', 'company_id', $this->integer()->unsigned()->null());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%contact}}', 'type');
        $this->alterColumn('{{%contact}}', 'company_id', $this->integer()->unsigned()->notNull());
    }
}
