<?php

use yii\db\Migration;

/**
 * Handles the creation for table `company_driver`.
 */
class m161031_114808_create_company_driver_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_driver}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string(255),
            'phone' => $this->string(255),
            'mark_id' => $this->integer()->unsigned(),
            'type_id' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%company_driver}}');
    }
}
