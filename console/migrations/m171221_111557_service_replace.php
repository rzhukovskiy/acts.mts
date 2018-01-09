<?php

use yii\db\Migration;

class m171221_111557_service_replace extends Migration
{
    public function up()
    {
        $this->createTable('{{%service_replace}}', [
            'id' => $this->primaryKey(),
            'client_id' => 'int(11) NOT NULL',
            'partner_id' => 'int(11) NOT NULL',
            'type' => 'smallint(6) NOT NULL',
            'type_client' => 'smallint(6) NOT NULL DEFAULT 0',
            'type_partner' => 'smallint(6) NOT NULL DEFAULT 0',
            'created_at' => 'int(11) NOT NULL',
            'updated_at' => 'int(11) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

        $this->createTable('{{%service_replace_item}}', [
            'id' => $this->primaryKey(),
            'replace_id' => 'int(11) NOT NULL',
            'service_id' => 'smallint(6) NOT NULL',
            'company_id' => 'int(11) NOT NULL',
            'type' => 'smallint(6) NOT NULL',
            'car_type' => 'smallint(6) NOT NULL DEFAULT 0',
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');

    }

    public function down()
    {
        $this->dropTable('{{%service_replace}}');
        $this->dropTable('{{%service_replace_item}}');
    }
}
