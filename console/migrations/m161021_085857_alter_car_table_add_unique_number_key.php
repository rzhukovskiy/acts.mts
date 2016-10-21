<?php

use yii\db\Migration;

class m161021_085857_alter_car_table_add_unique_number_key extends Migration
{
    public function up()
    {
        $this->dropIndex('idx_number_01_00', '{{%car}}');
        $this->createIndex('idx_number_01_00', '{{%car}}', 'number', 1);
    }

    public function down()
    {
        $this->dropIndex('idx_number_01_00', '{{%car}}');
        $this->createIndex('idx_number_01_00', '{{%car}}', 'number', 0);
    }

}
