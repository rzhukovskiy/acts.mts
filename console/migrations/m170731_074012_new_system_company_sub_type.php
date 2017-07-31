<?php

use yii\db\Migration;

class m170731_074012_new_system_company_sub_type extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%company}}', 'sub_type');

        $this->createTable('{{%company_sub_type}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11)->notNull(),
            'sub_type' => "tinyint(1) NOT NULL",
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->addColumn('{{%company}}', 'sub_type', "smallint(3) DEFAULT 0 NOT NULL");
        $this->dropTable('{{%company_sub_type}}');
    }
}
