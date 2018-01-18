<?php

use yii\db\Migration;

class m180111_080418_new_table_track_info extends Migration
{
    public function up()
    {
        $this->createTable('{{%tracker_info}}', [
            'id' => $this->primaryKey(),
            'type' => 'tinyint(1) NOT NULL',
            'second_id' => 'int(11) NOT NULL',
            'number' => 'varchar(255) NOT NULL',
            'value' => 'tinyint(1) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%tracker_info}}');
    }
}
