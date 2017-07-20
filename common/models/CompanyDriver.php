<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_driver}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $name
 * @property string $phone
 * @property integer $car_id
 *
 * @property Company $company
 * @property Car $car
 */
class CompanyDriver extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_driver}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'car_id', 'phone', 'name'], 'required'],
            [['company_id', 'car_id'], 'integer'],
            [['phone', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'company_id' => 'Company ID',
            'name'       => 'Фио',
            'phone'       => 'Номер телефона',
            'car_id'       => 'ТС',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'car_id']);
    }

}
