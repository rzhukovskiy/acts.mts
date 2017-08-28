<?php

use yii\db\Migration;

class m170817_122206_create_api_token extends Migration
{
    public function up()
    {

        $this->createTable('{{%api_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'token' => $this->string()->notNull()->unique(),
            'expired_at' => 'varchar(20) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%api_token}}');
    }

}
