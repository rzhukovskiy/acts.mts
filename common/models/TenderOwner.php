<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tender_owner".
 *
 * @property integer $id
 * @property string $text
 * @property string $data
 * @property integer $tender_user
 * @property integer $tender_id
 */
class TenderOwner extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender_owner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text'], 'required'],
            [['text'], 'string'],
            [['data'], 'string', 'max' => 20],
            [['tender_user', 'tender_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Текст',
            'tender_user' => 'Ответственный сотрудник',
            'tender_id' => 'ID Тендер',
            'data' => 'Дата',
        ];
    }
    /* Связь с моделью User*/

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'tender_user']);
    }
    /* Геттер для названия User */
    public function getUserName()
    {
        return $this->user->username;
    }
}
