<?php

    use yii\db\Migration;

    class m160809_135225_cards_added extends Migration
    {
        public function up()
        {
            $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

            $this->createTable( '{{%card}}', [
                'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                0 => 'PRIMARY KEY (`id`)',
                'company_id' => 'INT(10) UNSIGNED NOT NULL',
                'number' => 'INT(11) NOT NULL',
                'status' => 'SMALLINT(6) NOT NULL DEFAULT \'10\'',
                'created_at' => 'DATETIME NOT NULL',
                'updated_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ], $tableOptions_mysql );


            $this->createIndex( 'idx_UNIQUE_number_4304_00', '{{%card}}', 'number', 1 );
            $this->createIndex( 'idx_company_id_4304_01', '{{%card}}', 'company_id', 0 );

            $this->execute( 'SET foreign_key_checks = 0' );
            // TODO: maybe RESTRICT on delete
            //$this->addForeignKey( 'fk_acts_company_4295_00', '{{%card}}', 'company_id', '{{%card}}', 'id', 'CASCADE', 'CASCADE' );
            $this->execute( 'SET foreign_key_checks = 1;' );
        }

        public function down()
        {
            $this->execute( 'SET foreign_key_checks = 0' );
            $this->dropTable('{{$card}}');
            $this->execute( 'SET foreign_key_checks = 1;' );
        }
    }
