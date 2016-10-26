<?php

use yii\db\Migration;

class m161026_084726_add_foreign_keys_in_company_member_and_company_info extends Migration
{
    public function up()
    {

        $this->createIndex('company_id', '{{%company_member}}', 'company_id');
        $this->addForeignKey('company_member_company_id',
            '{{%company_member}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE');

        $this->createIndex('company_id', '{{%company_info}}', 'company_id');
        $this->addForeignKey('company_info_company_id',
            '{{%company_info}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('company_member_company_id', '{{%company_member}}');
        $this->dropIndex('company_id', '{{%company_member}}');

        $this->dropForeignKey('company_info_company_id', '{{%company_info}}');
        $this->dropIndex('company_id', '{{%company_info}}');
    }

}
