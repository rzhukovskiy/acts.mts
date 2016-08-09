<?php

use yii\db\Migration;

/**
 * Handles the creation for table `car`.
 */
class m160809_130653_create_car_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        /* MYSQL */
        if (!in_array('car', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%car}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'company_id' => 'INT(10) UNSIGNED NOT NULL',
                    'number' => 'VARCHAR(45) NOT NULL',
                    'mark_id' => 'INT(10) UNSIGNED NULL',
                    'type_id' => 'INT(10) UNSIGNED NULL',
                    'is_infected' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\'',
                ], $tableOptions_mysql);
            }
        }

        $this->createIndex('idx_number_01_00','{{%car}}','number',0);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%car}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
