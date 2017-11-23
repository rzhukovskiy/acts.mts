<?php

use yii\db\Migration;

class m171120_114850_insert_inn_company_info extends Migration
{
    public function up()
    {
        $this->addColumn('{{%company_info}}', 'inn', 'varchar(30) DEFAULT NULL');
        $this->addColumn('{{%car}}', 'cert', 'varchar(150) DEFAULT NULL');
        $this->addColumn('{{%car}}', 'is_penalty', 'tinyint(1) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%company_info}}','inn');
        $this->dropColumn('{{%car}}','cert');
        $this->dropColumn('{{%car}}','is_penalty');
    }
}
