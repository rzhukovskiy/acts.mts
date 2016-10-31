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
 * @property integer $mark_id
 * @property integer $type_id
 * 
 * @property Company $company
 * @property Mark $mark
 * @property Type $type
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
            [['company_id'], 'required'],
            [['company_id', 'mark_id', 'type_id'], 'integer'],
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
            'phone'      => 'Телефон',
            'type_id'    => 'Тип ТС',
            'mark_id'    => 'Марка ТС',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getMark(  )
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }
}
