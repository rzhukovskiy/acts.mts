<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "history_checks".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_id
 * @property string $serial_number
 * @property string $date_send
 */
class HistoryChecks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%history_checks}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_id', 'date_send'], 'required'],
            [['company_id', 'user_id'], 'integer'],
            [['date_send'], 'string', 'max' => 20],
            [['serial_number'], 'string', 'max' => 70],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Мойка',
            'user_id' => 'Пользователь',
            'serial_number' => 'Серийный номер чеков',
            'date_send' => 'Дата отправки',
        ];
    }

    public function beforeSave($insert)
    {
        // Если это новая запись то обрабатываем данные из формы здесь
        if ($this->isNewRecord) {
            // переводим дату в нужный формат
            $this->date_send = (String) strtotime($this->date_send);
        }
        return parent::beforeSave($insert);
    }
}
