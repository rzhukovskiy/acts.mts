<?php

use yii\db\Migration;

class m170619_072544_update_type_company_info_comment extends Migration
{

    public function up()
    {
        $this->alterColumn('{{%company_info}}', 'comment', 'text', 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->alterColumn('{{%company_info}}' ,'comment', 'varchar(255)', 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

}
