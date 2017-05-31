<?php

use yii\db\Migration;

class m170530_084401_update_act_scope_for_service extends Migration
{

    public function up()
    {

        $this->addColumn('{{%act_scope}}', 'parts', $this->integer(1) . "NOT NULL DEFAULT 0");
        $this->alterColumn('{{%act_scope}}','amount', 'DECIMAL(7,1) NOT NULL DEFAULT 0');

    }

    public function down()
    {
        $this->dropColumn('{{%act_scope}}', 'parts');
        $this->alterColumn('{{%act_scope}}','amount', 'smallint(6) NOT NULL DEFAULT 0');
    }

}
