<?php

use yii\db\Migration;

class m170613_104412_add_user_code_pass extends Migration
{

    public function up()
    {
        $this->addColumn('{{%user}}', 'code_pass', "varchar(80) DEFAULT NULL");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user}}', 'code_pass');
    }

}
