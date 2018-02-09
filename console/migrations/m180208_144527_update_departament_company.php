<?php

use yii\db\Migration;

class m180208_144527_update_departament_company extends Migration
{
    public function up()
    {
        $this->addColumn('{{%department_company}}','type_user', 'tinyint(1) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%department_company}}','type_user');
    }
}
