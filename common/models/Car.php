<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%car}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $number
 * @property integer $mark_id
 * @property integer $type_id
 * @property integer $is_infected
 */
class Car extends \yii\db\ActiveRecord
{
    public $carsCountByType;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%car}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'number'], 'required'],
            [['company_id', 'mark_id', 'type_id', 'is_infected'], 'integer'],
            [['number'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Компания',
            'number' => 'Номер',
            'mark_id' => 'Марка',
            'type_id' => 'Тип',
            'is_infected' => 'Дизенфицировать',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CarQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CarQuery(get_called_class());
    }

    // TODO: add relation for Company, Act

    public function getMark(  )
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }
}
