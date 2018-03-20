<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "informing_users".
 *
 * @property integer $id
 * @property integer $informing_id
 * @property integer $user_id
 * @property integer $status
 */
class InformingUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%informing_users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['informing_id', 'user_id'], 'required'],
            [['informing_id', 'user_id', 'status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'informing_id' => 'Informing ID',
            'user_id' => 'ID Пользователя',
            'status' => 'Статус',
        ];
    }
}
