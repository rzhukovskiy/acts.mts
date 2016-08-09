<?php

use yii\db\Migration;

/**
 * Handles the creation for table `price`.
 */
class m160809_133657_create_price_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        if (!in_array('price', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%price}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'type_id' => 'INT(10) UNSIGNED NOT NULL',
                    'company_id' => 'INT(10) UNSIGNED NOT NULL',
                    'inside' => 'INT(10) UNSIGNED NULL',
                    'outside' => 'INT(10) UNSIGNED NULL',
                    'disinfection' => 'INT(10) UNSIGNED NULL',
                    'engine' => 'INT(10) UNSIGNED NULL',
                ], $tableOptions_mysql);
            }
        }

        $this->createIndex('idx_type_id_23_00','{{%price}}','type_id',0);
        $this->createIndex('idx_company_id_23_01','{{%price}}','company_id',0);

        $this->execute('SET foreign_key_checks = 0');
        $this->addForeignKey('fk_company_23_00','{{%price}}', 'company_id', '{{%company}}', 'id', 'CASCADE', 'CASCADE' );
        $this->addForeignKey('fk_type_23_01','{{%price}}', 'type_id', '{{%type}}', 'id', 'CASCADE', 'CASCADE' );
        $this->execute('SET foreign_key_checks = 1;');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%price}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
