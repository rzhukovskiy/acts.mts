<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%act_export}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $type
 * @property integer $company
 * @property string $period
 * @property string $name
 * @property string $data_load
 */
class ActExport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_export}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'company', 'company_id'], 'integer'],
            [['period'], 'string', 'max' => 7],
            [['name'], 'string', 'max' => 255],
            [['data_load'], 'string', 'max' => 30],
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
            'company' => 'Партнер',
            'company_id' => 'ID компании',
            'period' => 'Период',
            'name' => 'Имя',
            'data_load' => 'Дата загрузки',
        ];
    }
}
