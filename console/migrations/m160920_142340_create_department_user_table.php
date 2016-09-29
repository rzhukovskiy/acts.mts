<?php

use yii\db\Migration;

/**
 * Handles the creation for table `department_user`.
 */
class m160920_142340_create_department_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%department_user}}', [
            'id' => $this->primaryKey(),
            'department_id' => $this->integer()->notNull()->unsigned(),
            'user_id' => $this->integer()->notNull()->unsigned(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%department_user}}');
    }
}
