<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Handles adding car_id to table `act`.
 */
class m170327_150136_add_car_id_column_to_act_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%act}}', 'car_id', $this->integer()->defaultValue(null));
        $this->createIndex('idx-act-car_id', '{{%act}}', 'car_id');
        $this->addColumn('{{%act}}', 'extra_car_id', $this->integer()->defaultValue(null));
        $this->createIndex('idx-act-extra_car_id', '{{%act}}', 'extra_car_id');
        $this->renameColumn('{{%act}}', 'number', 'car_number');
        $this->renameColumn('{{%act}}', 'extra_number', 'extra_car_number');
        $this->addColumn('{{%act}}', 'card_number', $this->integer()->defaultValue(null));
        $this->createIndex('idx-act-card_number', '{{%act}}', 'card_number');

        $this->update('{{%act}}', ['card_number' => new Expression('card_id')]);
        $this->update('{{%act}}', ['card_id' => null]);
        
        echo "    > update existing data ...";
        $time = microtime(true);
        Yii::$app->db->createCommand("UPDATE {{%act}} act, {{%card}} card SET card_id = card_number WHERE card_number = card.id")->execute();
        Yii::$app->db->createCommand("UPDATE {{%act}} act, {{%card}} card SET card_number = card.number WHERE card_id = card.id")->execute();
        Yii::$app->db->createCommand("UPDATE {{%act}} act, {{%car}} car SET car_id = car.id WHERE car_number = car.number")->execute();
        Yii::$app->db->createCommand("UPDATE {{%act}} act, {{%car}} car SET extra_car_id = car.id WHERE extra_car_number = car.number")->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->update('{{%act}}', ['card_id' => new Expression('card_number')], ['card_id' => 'null']);
        $this->dropColumn('{{%act}}', 'car_id');
        $this->dropColumn('{{%act}}', 'extra_car_id');
        $this->dropColumn('{{%act}}', 'card_number');
        $this->renameColumn('{{%act}}', 'car_number', 'number');
        $this->renameColumn('{{%act}}', 'extra_car_number', 'extra_number');
    }
}
