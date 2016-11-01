<?php

use yii\db\Migration;

class m161101_104123_create_department_user_company_type extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%department_user_company_type}}',
        [
            'id'             => $this->primaryKey(),
            'user_id'        => $this->integer()->notNull(),
            'company_type'   => $this->integer()->unsigned()->notNull(),
            'company_status' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%department_user_company_type}}');
    }
}
