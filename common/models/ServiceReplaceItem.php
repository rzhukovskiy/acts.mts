<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "service_replace_item".
 *
 * @property integer $id
 * @property integer $replace_id
 * @property integer $service_id
 * @property integer $company_id
 * @property integer $type
 * @property integer $car_type
 * @property integer $car_mark
 */
class ServiceReplaceItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service_replace_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['replace_id', 'service_id', 'company_id', 'type'], 'required'],
            [['replace_id', 'service_id', 'company_id', 'type', 'car_type', 'car_mark'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'replace_id' => 'Замещение услуг',
            'service_id' => 'Услуга',
            'company_id' => 'Компания',
            'type' => 'Тип',
            'car_type' => 'Тип ТС',
            'car_mark' => 'Марка ТС',
        ];
    }
}
