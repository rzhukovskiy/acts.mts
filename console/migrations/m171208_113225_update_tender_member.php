<?php

use yii\db\Migration;

class m171208_113225_update_tender_member extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%tender_members}}','tender_id');
        $this->dropColumn('{{%tender}}','inn_competitors');
        $this->dropColumn('{{%tender}}','competitor');
        $this->dropColumn('{{%tender_control}}','balance_work');
    }

    public function down()
    {
        $this->addColumn('{{%tender_members}}','tender_id', 'int(11) NOT NULL');
        $this->addColumn('{{%tender}}','inn_competitors', 'text DEFAULT NULL');
        $this->addColumn('{{%tender}}','competitor', 'varchar(255) DEFAULT NULL');
        $this->addColumn('{{%tender_control}}','balance_work', 'decimal(12,2) DEFAULT NULL');
    }

}
