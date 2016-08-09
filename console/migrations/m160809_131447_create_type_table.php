<?php

use yii\db\Migration;

/**
 * Handles the creation for table `type`.
 */
class m160809_131447_create_type_table extends Migration
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
        if (!in_array('type', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%type}}', [
                    'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'name' => 'VARCHAR(255) NOT NULL',
                    'image' => 'VARCHAR(45) NULL',
                ], $tableOptions_mysql);
            }
        }

        $this->createIndex('idx_UNIQUE_name_89_01','{{%type}}','name',1);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%type}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
