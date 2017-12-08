<?php

use yii\db\Migration;

class m171208_113225_update_tender_member extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender_members}}','tender_id', 'int(11) NOT NULL');
        $this->dropColumn('{{%tender}}','inn_competitors');
        $this->dropColumn('{{%tender}}','competitor');
        $this->dropColumn('{{%tender_control}}','balance_work');
    }

    public function down()
    {
        $this->dropColumn('{{%tender_members}}','tender_id');
        $this->addColumn('{{%tender}}','inn_competitors');
        $this->addColumn('{{%tender}}','competitor');
        $this->addColumn('{{%tender_control}}','balance_work');
    }

}
