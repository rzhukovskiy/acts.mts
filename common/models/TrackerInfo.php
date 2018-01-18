<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tracker_info".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $second_id
 * @property string $number
 * @property string $value
 */
class TrackerInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tracker_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'second_id', 'number', 'value'], 'required'],
            [['type', 'second_id', 'value'], 'integer'],
            [['number'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип',
            'second_id' => 'Second ID',
            'number' => 'Номер отслеживания',
            'value' => 'Значение',
        ];
    }
}
