<?php

use yii\db\Migration;

class m161021_093643_create_table_contact extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%contact}}',
            [
                'id'          => $this->primaryKey(),
                'company_id'  => $this->integer()->unsigned()->notNull(),
                'name'        => $this->string(255),
                'description' => $this->string(1000),
            ]);

        $this->createIndex('company_id', '{{%contact}}', 'company_id', true);
        $this->addForeignKey('contact_company_id',
            '{{%contact}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%contact}}');
    }
}
