<?php

use yii\db\Migration;

class m160822_094803_add_column_image_to_table_mark extends Migration
{
    public function up()
    {
        $this->addColumn('{{%type}}', 'image', $this->string(150)->after('name')->null());
    }

    public function down()
    {
        $this->dropColumn('{{%type}}', 'image');
    }
}
