<?php

use yii\db\Migration;

class m171226_075909_update_expence_company extends Migration
{
    public function up()
    {
        $this->addColumn('{{%expense_company}}','position', 'varchar(255)');

    }

    public function down()
    {
        $this->dropColumn('{{%expense_company}}','position');
    }

}
