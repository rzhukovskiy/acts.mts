<?php

use yii\db\Migration;

/**
 * Handles the creation for table `mark`.
 */
class m160809_131051_create_mark_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        $this->createTable('{{%mark}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            0 => 'PRIMARY KEY (`id`)',
            'name' => 'VARCHAR(45) NOT NULL',
        ], $tableOptions_mysql);

        $this->createIndex('idx_UNIQUE_name_88_00', '{{%mark}}', 'name', 1);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%mark}}');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
