<?php

use yii\db\Migration;

/**
 * Handles the creation of table `company_member`.
 */
class m161023_165328_create_company_member_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_member}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'position' => $this->string(255),
            'phone' => $this->string(255),
            'email' => $this->string(255),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%company_member}}');
    }
}
