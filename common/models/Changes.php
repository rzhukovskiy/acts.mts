<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "changes".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $sub_type
 * @property integer $user_id
 * @property integer $company_id
 * @property integer $type_id
 * @property string $old_value
 * @property string $new_value
 * @property integer $status
 * @property string $date
 */
class Changes extends ActiveRecord
{

    // Type
    const TYPE_CARD = 1;
    const TYPE_PRICE = 2;

    // Status
    const NEW_PRICE = 1;
    const EDIT_PRICE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%changes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id', 'old_value', 'new_value', 'status'], 'required'],
            [['type', 'sub_type', 'user_id', 'status', 'company_id', 'type_id'], 'integer'],
            [['date'], 'safe'],
            [['old_value', 'new_value'], 'string', 'max' => 255],
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
            'sub_type' => 'Подтип',
            'user_id' => 'Сотрудник',
            'company_id' => 'Компания',
            'type_id' => 'Тип ТС',
            'old_value' => 'Старое значение',
            'new_value' => 'Новое значение',
            'status' => 'Действие',
            'date' => 'Дата',
        ];
    }
}
