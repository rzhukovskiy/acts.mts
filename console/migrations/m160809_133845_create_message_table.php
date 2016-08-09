<?php

use yii\db\Migration;

/**
 * Handles the creation for table `message`.
 */
class m160809_133845_create_message_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";

        if (!in_array('message', $tables))  {
            if ($dbType == "mysql") {
                $this->createTable('{{%message}}', [
                    'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                    0 => 'PRIMARY KEY (`id`)',
                    'parent_id' => 'INT(10) UNSIGNED NULL',
                    'from' => 'INT(11) NOT NULL',
                    'to' => 'INT(11) NOT NULL',
                    'create_date' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ',
                    'text' => 'MEDIUMTEXT NOT NULL',
                    'is_read' => 'TINYINT(1) NOT NULL',
                ], $tableOptions_mysql);
            }
        }

        $this->createIndex('idx_parent_id_6_00','{{%message}}','parent_id',0);

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%message}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
