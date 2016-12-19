<?php

namespace common\models;

use common\models\query\CompanyTimeQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_time}}".
 *
 * @property integer $id
 * @property string $company_id
 * @property string $day
 * @property string $start_at
 * @property string $end_at
 * @property Company $company
 */
class CompanyTime extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_time}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'day', 'start_at', 'end_at'], 'integer'],
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
            'day' => 'День недели',
            'start_at' => 'Начало работы',
            'end_at' => 'Конец работы',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @inheritdoc
     * @return CompanyTimeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CompanyTimeQuery(get_called_class());
    }
}
