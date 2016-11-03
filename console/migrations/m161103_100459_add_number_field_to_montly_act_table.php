<?php

use yii\db\Migration;

class m161103_100459_add_number_field_to_montly_act_table extends Migration
{

    public function up()
    {
        $this->addColumn('{{%monthly_act}}', 'number', $this->string(45)->null()->after('profit'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%monthly_act}}', 'number');
    }
}
