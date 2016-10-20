<?php

use yii\db\Migration;

/**
 * Handles the creation for table `company_offer`.
 */
class m161020_150512_create_company_offer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_offer}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'process' => $this->string(1000),
            'mail_number' => $this->string(255),
            'communication_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%company_offer}}');
    }
}
