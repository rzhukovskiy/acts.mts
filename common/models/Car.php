<?php

namespace common\models;

use common\models\query\CarQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%car}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $number
 * @property integer $mark_id
 * @property integer $type_id
 * @property integer $is_infected
 *
 * @property Company $company
 * @property Mark $mark
 * @property Type $type
 * @property Act $acts
 */
class Car extends ActiveRecord
{
    const SCENARIO_INFECTED = 'infected';
    const SCENARIO_OWNER = 'owner';
    
    public $carsCountByType;
    public $listService;

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
            ['is_infected', 'default', 'value' => 0],
            [['number'], 'unique'],
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
            'period' => 'Период',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CarQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CarQuery(get_called_class());
    }

    public function getMark(  )
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getActs()
    {
        return $this->hasMany(Act::className(), ['number' => 'number']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $listAct = Act::findAll(['status' => Act::STATUS_NEW, 'number' => $this->number]);
            foreach ($listAct as $act) {
                $act->save();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
