<?php

use yii\db\Migration;

/**
 * Handles the creation for table `company_info`.
 */
class m161009_143051_create_company_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_info}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull()->unsigned(),
            'phone' => $this->string(255),
            'address' => $this->string(255),
            'address_mail' => $this->string(255),
            'email' => $this->string(255),
            'start_at' => $this->integer()->unsigned(),
            'end_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%company_info}}');
    }
}
