<?php

use yii\db\Migration;

/**
 * Handles adding code to table `user`.
 */
class m170330_120050_add_code_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%user}}', 'code', $this->integer()->defaultValue(null));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user}}', 'code');
    }
}
