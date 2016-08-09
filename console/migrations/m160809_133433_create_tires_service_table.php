<?php

use yii\db\Migration;

/**
 * Handles the creation for table `tires_service`.
 */
class m160809_133433_create_tires_service_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        if (!in_array('tires_service', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%tires_service}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'description' => 'VARCHAR(255) NULL',
                    'is_fixed' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\'',
                    'pos' => 'INT(10) UNSIGNED NOT NULL',
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
        $this->dropTable('{{%tires_service}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
