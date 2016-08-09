<?php

use yii\db\Migration;

/**
 * Handles the creation for table `requisites`.
 */
class m160809_133301_create_requisites_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        if (!in_array('requisites', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%requisites}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'company_id' => 'INT(10) UNSIGNED NOT NULL',
                    'header' => 'TEXT NULL',
                    'contract' => 'VARCHAR(255) NULL',
                    'service_type' => 'VARCHAR(45) NULL',
                ], $tableOptions_mysql);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%requisites}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
