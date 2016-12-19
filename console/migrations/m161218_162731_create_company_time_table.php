<?php

use yii\db\Migration;

/**
 * Handles the creation of table `company_time`.
 */
class m161218_162731_create_company_time_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_time}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned(),
            'day' => $this->integer()->unsigned(),
            'start_at' => $this->integer()->unsigned(),
            'end_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%company_time}}');
    }
}
