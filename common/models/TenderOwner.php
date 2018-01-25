<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tender_owner".
 *
 * @property integer $id
 * @property string $text
 * @property string $data
 * @property string $link
 * @property string $city
 * @property string $date_from
 * @property string $date_to
 * @property integer $tender_user
 * @property integer $tender_id
 * @property float $purchase
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
            [['purchase'], 'safe'],
            [['text', 'reason_not_take'], 'string', 'max' => 5000],
            [['data', 'date_from', 'date_to'], 'string', 'max' => 20],
            [['link', 'city'], 'string', 'max' => 255],
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
            'data' => 'Дата закрепления',
            'link' => 'Документация',
            'city' => 'Город',
            'purchase' => 'Сумма закупки',
            'date_from' => 'Дата начала',
            'date_to' => 'Дата окончания',
            'reason_not_take' => 'Комментарий',
        ];
    }

    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if($this->isNewRecord) {
            $this->date_from = (String) strtotime($this->date_from);
            $this->date_to = (String) strtotime($this->date_to);
        }
        return parent::beforeSave($insert);

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
