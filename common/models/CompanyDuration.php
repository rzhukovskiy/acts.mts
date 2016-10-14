<?php

namespace common\models;

use common\models\query\CompanyDurationQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_duration}}".
 *
 * @property integer $id
 * @property string $company_id
 * @property string $type_id
 * @property string $duration
 * @property string $created_at
 * @property string $updated_at
 */
class CompanyDuration extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_duration}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'type_id'], 'required'],
            [['company_id', 'type_id', 'duration'], 'integer'],
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
            'type_id'    => 'Тип ТС',
            'duration'   => 'Длительность мойки',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CompanyDurationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CompanyDurationQuery(get_called_class());
    }
}
