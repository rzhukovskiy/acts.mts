<?php

use yii\db\Migration;

class m180115_135327_update_tender extends Migration
{
    public function up()
    {
        $this->addColumn('{{%tender}}','last_sentence_nds', 'DECIMAL(12,2) DEFAULT NULL');
        $this->addColumn('{{%tender}}','last_sentence_nonds', 'DECIMAL(12,2) DEFAULT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%tender}}','last_sentence_nds');
        $this->dropColumn('{{%tender}}','last_sentence_nonds');

    }
}
