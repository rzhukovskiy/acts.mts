<?php

use yii\db\Migration;

class m170417_103736_add_company_info_comment extends Migration
{
    public function up()
    {

        $this->addColumn('{{%company_info}}', 'comment', $this->string(255) . "DEFAULT NULL");

    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}', 'comment');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
