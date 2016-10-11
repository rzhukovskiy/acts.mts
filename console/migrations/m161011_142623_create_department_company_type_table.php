<?php

use yii\db\Migration;

/**
 * Handles the creation for table `department_company_type`.
 */
class m161011_142623_create_department_company_type_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%department_company_type}}', [
            'id' => $this->primaryKey(),
            'department_id' => $this->integer()->unsigned()->notNull(),
            'company_type' => $this->integer()->unsigned()->notNull(),
            'company_status' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%department_company_type}}');
    }
}
