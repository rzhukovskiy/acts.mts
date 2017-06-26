<?php

namespace common\models;

use yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "car_history".
 *
 * @property integer $id
 * @property integer $from
 * @property integer $to
 * @property integer $user_id
 * @property integer $car_id
 * @property string $car_number
 * @property integer $type
 * @property string $date
 */

class CarHistory extends ActiveRecord
{

    static $listType = [
        'Добавление',
        'Удаление',
        'Перемещение',
    ];

    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%car_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from', 'user_id', 'car_id', 'type', 'date'], 'required'],
            [['from', 'to', 'user_id', 'car_id', 'type'], 'integer'],
            [['date'], 'string', 'max' => 20],
            [['car_number'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from' => 'Прежняя компания',
            'to' => 'Новая компания',
            'user_id' => 'Сотрудник',
            'car_id' => 'Номер ТС',
            'car_number' => 'Номер ТС',
            'type' => 'Действие',
            'date' => 'Дата',
        ];
    }

    public function getFromCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'from']);
    }

    public function getToCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'to']);
    }

}
