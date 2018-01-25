<?php

use yii\db\Migration;

class m180125_095750_addcolumn_task_and_tenderowner extends Migration
{
    public function up()
    {
        $this->addColumn('{{%task_user}}','comment_main', 'text');
        $this->addColumn('{{%task_user}}','comment_watcher', 'text');
        $this->addColumn('{{%tender_owner}}','purchase', 'decimal(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender_owner}}','date_from', 'varchar(20) DEFAULT NULL');
        $this->addColumn('{{%tender_owner}}','date_to', 'varchar(20) DEFAULT NULL');
        $this->addColumn('{{%tender_owner}}','city', 'varchar(255) DEFAULT NULL');
        $this->addColumn('{{%tender_owner}}','user_comment', 'int(11)');

    }

    public function down()
    {
        $this->dropColumn('{{%task_user}}','comment_main');
        $this->dropColumn('{{%task_user}}','comment_watcher');
        $this->dropColumn('{{%tender_owner}}','purchase');
        $this->dropColumn('{{%tender_owner}}','date_from');
        $this->dropColumn('{{%tender_owner}}','date_to');
        $this->dropColumn('{{%tender_owner}}','city');
        $this->dropColumn('{{%tender_owner}}','user_comment');
    }

}