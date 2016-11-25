<?php

use yii\db\Migration;

class m161125_185132_add_is_act_sign_to_company_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%company}}', 'is_act_sign', $this->boolean()->notNull()->defaultValue(true));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%company}}', 'is_act_sign');
    }
}
