<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "act_data".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $company
 * @property string $period
 * @property string $name
 * @property string $number
 */
class ActData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'company'], 'integer'],
            [['period'], 'string', 'max' => 7],
            [['name'], 'string', 'max' => 255],
            [['number'], 'string', 'max' => 150],
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
            'company' => 'Компания',
            'period' => 'Период',
            'name' => 'Имя файла',
            'number' => 'Номер документа',
        ];
    }
}
