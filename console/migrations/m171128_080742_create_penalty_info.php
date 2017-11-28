<?php

use yii\db\Migration;

class m171128_080742_create_penalty_info extends Migration
{
    public function up()
    {
        $this->createTable('{{%penalty_info}}', [
            'id' => $this->primaryKey(),
            'pen_id' => 'int(11) NOT NULL',
            'car_id' => 'int(11) NOT NULL',
            'description' => 'text NOT NULL',
            'postNumber' => 'varchar(30) NOT NULL',
            'postedAt' => 'varchar(20) NOT NULL',
            'violationAt' => 'varchar(30) NOT NULL',
            'amount' => 'decimal(12,2) NOT NULL',
            'totalAmount' => 'decimal(12,2) NOT NULL',
            'isDiscount' => 'tinyint(1) NOT NULL',
            'discountDate' => 'varchar(30)',
            'discountSize' => 'varchar(5)',
            'isExpired' => 'tinyint(1) NOT NULL',
            'penaltyDate' => 'varchar(30) NOT NULL',
            'isPaid' => 'tinyint(1) NOT NULL',
            'docType' => 'varchar(30) NOT NULL',
            'docNumber' => 'varchar(20) NOT NULL',
            'enablePics' => 'tinyint(1) NOT NULL',
            'pics' => 'text'
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }

    public function down()
    {
        $this->dropTable('{{%penalty_info}}');
    }

}