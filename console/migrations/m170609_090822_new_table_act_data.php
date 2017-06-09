<?php

use yii\db\Migration;

class m170609_090822_new_table_act_data extends Migration
{

    public function up()
    {
        $this->createTable('{{%act_data}}', [
            'id' => $this->primaryKey(),
            'type' => $this->integer(2)->notNull(),
            'company' => $this->integer(1)->notNull(),
            'period' => $this->string(7)->notNull(),
            'name' => $this->string(255)->notNull(),
            'number' => $this->string(150)
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%act_data}}');
    }

}
