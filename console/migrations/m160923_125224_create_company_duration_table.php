<?php

use yii\db\Migration;

/**
 * Handles the creation for table `company_duration`.
 */
class m160923_125224_create_company_duration_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_duration}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'type_id' => $this->integer()->unsigned()->notNull(),
            'duration' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%company_duration}}');
    }
}
