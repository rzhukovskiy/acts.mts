<?php

use yii\db\Migration;

class m161025_092152_create_form_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%company_client}}',
            [
                'id'         => $this->primaryKey(),
                'company_id' => $this->integer()->unsigned()->notNull(),
                'name'       => $this->string(255),
                'phone'      => $this->string(255),
            ]);

        $this->createTable('{{%company_attributes}}',
            [
                'id'         => $this->primaryKey(),
                'company_id' => $this->integer()->unsigned()->notNull(),
                'name'       => $this->string(255),
                'type'       => $this->smallInteger(),
                'value'      => $this->text(),
            ]);

        $this->createIndex('company_id', '{{%company_client}}', 'company_id');
        $this->addForeignKey('company_client_company_id',
            '{{%company_client}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE');

        $this->createIndex('company_id', '{{%company_attributes}}', 'company_id');
        $this->addForeignKey('company_attributes_company_id',
            '{{%company_attributes}}',
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
        $this->dropTable('{{%company_client}}');
        $this->dropTable('{{%company_attributes}}');
    }
}
