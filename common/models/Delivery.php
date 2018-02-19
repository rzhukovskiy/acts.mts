<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "delivery".
 *
 * @property integer $id
 * @property string $wash_name
 * @property string $date_send
 * @property string $size
 * @property string $city
 */
class Delivery extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wash_name'], 'required'],
            [['wash_name', 'city'], 'string', 'max' => 255],
            [['date_send', 'size'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wash_name' => 'Название мойки',
            'date_send' => 'Дата отправки',
            'size' => 'Литраж',
            'city' => 'Город',
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
