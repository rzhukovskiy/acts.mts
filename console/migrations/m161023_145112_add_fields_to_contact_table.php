<?php

use yii\db\Migration;

class m161023_145112_add_fields_to_contact_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%contact}}', 'phone', $this->string());
        $this->addColumn('{{%contact}}', 'email', $this->string());
        $this->addColumn('{{%contact}}', 'position', $this->string());
        $this->dropColumn('{{%contact}}', 'description');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn('{{%contact}}', 'description', $this->string(1000));
        $this->dropColumn('{{%contact}}', 'email');
        $this->dropColumn('{{%contact}}', 'phone');
        $this->dropColumn('{{%contact}}', 'position');
    }
}
