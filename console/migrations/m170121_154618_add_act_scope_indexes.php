<?php

use yii\db\Migration;

class m170121_154618_add_act_scope_indexes extends Migration
{
    public function up()
    {
        $this->createIndex ('act_scope_act_id_index', '{{%act_scope}}', ['act_id' , 'company_id']);
    }

    public function down()
    {
        $this->dropIndex('act_scope_act_id_index', '{{%act_scope}}');
    }
}
