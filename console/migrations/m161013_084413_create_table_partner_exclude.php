<?php

use yii\db\Migration;

class m161013_084413_create_table_partner_exclude extends Migration
{
    public function up()
    {
        $this->createTable('{{%partner_exclude}}',
            [
                'id'         => $this->primaryKey(),
                'client_id'  => $this->integer()->unsigned()->notNull(),
                'partner_id' => $this->integer()->unsigned()->notNull(),
            ]);
        $this->createIndex('client_id', '{{%partner_exclude}}', 'client_id');
        $this->addForeignKey('partner_exclude_client_id',
            '{{%partner_exclude}}',
            'client_id',
            'company',
            'id',
            'CASCADE');
        $this->createIndex('partner_id', '{{%partner_exclude}}', 'partner_id');
        $this->addForeignKey('partner_exclude_partner_id',
            '{{%partner_exclude}}',
            'partner_id',
            'company',
            'id',
            'CASCADE');

    }

    public function down()
    {
        $this->dropTable('{{%client_exclude}}');
    }
}
