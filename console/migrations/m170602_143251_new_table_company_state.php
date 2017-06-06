<?php

use yii\db\Migration;

class m170602_143251_new_table_company_state extends Migration
{
    public function up()
    {
        $this->createTable('{{%company_state}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(11)->notNull(),
            'member_id' => $this->integer(11)->notNull(),
            'author_id' => $this->integer(11)->notNull(),
            'type' => "tinyint(1) NOT NULL",
            'comment' => $this->text()->notNull(),
            'date' => 'varchar(20) NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%company_state}}');
    }
}
