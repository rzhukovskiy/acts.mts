<?php

use yii\db\Migration;

/**
 * Handles the creation for table `extra_price`.
 */
class m160809_134013_create_extra_price_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        if (!in_array('extra_price', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%extra_price}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'price_id' => 'INT(10) UNSIGNED NOT NULL',
                    'inside' => 'INT(10) UNSIGNED NOT NULL',
                    'outside' => 'INT(10) UNSIGNED NOT NULL',
                ], $tableOptions_mysql);
            }
        }


        $this->createIndex('idx_price_id_24_00','{{%extra_price}}','price_id',0);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%extra_price}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
