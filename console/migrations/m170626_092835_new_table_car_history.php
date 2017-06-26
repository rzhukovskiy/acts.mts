<?php

use yii\db\Migration;

class m170626_092835_new_table_car_history extends Migration
{

    public function up()
    {
        $this->createTable('{{%car_history}}', [
            'id' => $this->primaryKey(),
            'from' => $this->integer(11)->notNull(),
            'to' => $this->integer(11)->notNull()->defaultValue(0),
            'user_id' => $this->integer(11)->notNull(),
            'car_id' => $this->integer(11)->notNull(),
            'car_number' => 'varchar(50) NOT NULL',
            'type' => "tinyint(1) NOT NULL",
            'date' => 'varchar(20) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%car_history}}');
    }

}
