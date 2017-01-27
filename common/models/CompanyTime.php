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
 *
 * @property Company $company
 */
class CompanyTime extends ActiveRecord
{
    const TYPE_WHOLEDAY = 0;
    const TYPE_EVERYDAY = 1;
    const TYPE_ANYDAY = 2;
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
    
    public function __toString()
    {
        return $this->end_at - $this->start_at == 86400
            ? 'круглосуточно'
            : gmdate('H:i', $this->start_at) . ' - ' . gmdate('H:i', $this->end_at);
    }
}
