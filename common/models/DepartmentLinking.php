<?php

namespace common\models;

use yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "department_linking".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $user_id
 * @property integer $company_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class DepartmentLinking extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%department_linking}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id', 'company_id'], 'required'],
            [['type', 'user_id', 'company_id'], 'integer'],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип',
            'user_id' => 'Сотрудник',
            'company_id' => 'Компания',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }
}
