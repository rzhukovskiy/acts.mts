<?php

use yii\db\Migration;

class m161017_075431_create_table_monthly_act extends Migration
{
    public function up()
    {
        $this->createTable('{{%monthly_act}}',
            [
                'id'             => $this->primaryKey(),
                'client_id'      => $this->integer()->unsigned()->notNull(),
                'type_id'        => $this->integer()->unsigned()->notNull(),
                'profit'         => $this->integer(),
                'payment_status' => $this->smallInteger(),
                'payment_date'   => $this->integer(),
                'act_status'     => $this->smallInteger(),
                'img'            => $this->text(),
                'created_at'     => $this->integer(),
                'updated_at'     => $this->integer(),
            ]);

        $this->createIndex('client_id', '{{%monthly_act}}', 'client_id');
        $this->addForeignKey('monthly_act_client_id',
            '{{%monthly_act}}',
            'client_id',
            'company',
            'id',
            'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%monthly_act}}');
    }

}
