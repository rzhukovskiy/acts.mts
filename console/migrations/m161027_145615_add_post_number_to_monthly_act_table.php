<?php

use yii\db\Migration;

class m161027_145615_add_post_number_to_monthly_act_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%monthly_act}}', 'post_number', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%monthly_act}}', 'post_number');
    }
}
