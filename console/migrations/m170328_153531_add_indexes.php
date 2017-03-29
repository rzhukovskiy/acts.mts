<?php

use yii\db\Migration;

class m170328_153531_add_indexes extends Migration
{
    public function up()
    {
        $this->createIndex('idx-act-service_type',  '{{%act}}',  'service_type');
        $this->createIndex('idx-act-served_at',     '{{%act}}',  'served_at');
        $this->createIndex('idx-act-car_number',    '{{%act}}',  'car_number');
        $this->createIndex('idx-act-client_id',     '{{%act}}',  'client_id');
        $this->createIndex('idx-act-partner_id',    '{{%act}}',  'partner_id');
        $this->createIndex('idx-act-type_id',       '{{%act}}',  'type_id');
        $this->createIndex('idx-act-mark_id',       '{{%act}}',  'mark_id');

        $this->createIndex('idx-car-company_id',    '{{%car}}',  'company_id');
        $this->createIndex('idx-car-type_id',       '{{%car}}',  'type_id');
        $this->createIndex('idx-car-mark_id',       '{{%car}}',  'mark_id');
        $this->createIndex('idx-car-number',        '{{%car}}',  'number');

        $this->createIndex('idx-card-company_id',   '{{%card}}', 'company_id');
        $this->createIndex('idx-card-number',       '{{%card}}', 'number');
    }

    public function down()
    {
        $this->dropIndex('idx-act-service_type',    '{{%act}}');
        $this->dropIndex('idx-act-served_at',       '{{%act}}');
        $this->dropIndex('idx-act-car_number',      '{{%act}}');
        $this->dropIndex('idx-act-client_id',       '{{%act}}');
        $this->dropIndex('idx-act-partner_id',      '{{%act}}');
        $this->dropIndex('idx-act-type_id',         '{{%act}}');
        $this->dropIndex('idx-act-mark_id',         '{{%act}}');

        $this->dropIndex('idx-car-company_id',      '{{%car}}');
        $this->dropIndex('idx-car-type_id',         '{{%car}}');
        $this->dropIndex('idx-car-mark_id',         '{{%car}}');
        $this->dropIndex('idx-car-number',          '{{%car}}');

        $this->dropIndex('idx-card-company_id',     '{{%card}}');
        $this->dropIndex('idx-card-number',         '{{%card}}');
    }
}
