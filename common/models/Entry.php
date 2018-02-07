<?php

namespace common\models;

use common\models\query\EntryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%entry}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $type_id
 * @property integer $card_id
 * @property integer $mark_id
 * @property integer $act_id
 * @property integer $service_type
 * @property integer $status
 * @property string $number
 * @property string $phone
 * @property string $extra_number
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $user_id
 *
 * @property string $start_str
 * @property string $end_str
 * @property string $day
 * @property string $card_number
 *
 * @property Company $company
 * @property Card $card
 * @property Type $type
 * @property Mark $mark
 * @property User $user
 */
class Entry extends ActiveRecord
{
    const STATUS_NOT_APPROVED = 0;
    const STATUS_APPROVED = 1;
    
    public $day;
    public $start_str;
    public $end_str;
    public $defaultDuration = 3600;
    
    public static $listStatus = [
        self::STATUS_NOT_APPROVED => 'не записан',
        self::STATUS_APPROVED => 'записан',
    ];

    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год', 'день'];

    private $card_number;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%entry}}';
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
            [['company_id', 'type_id', 'card_id', 'mark_id', 'service_type', 'status', 'created_at', 'updated_at', 'start_at', 'end_at', 'card_number'], 'integer'],
            [['company_id'], 'required'],
            [['number', 'extra_number', 'start_str', 'end_str', 'day'], 'string', 'max' => 45],
            [['phone'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Организация',
            'card_id' => 'Карта',
            'card_number' => 'Карта',
            'number' => 'Госномер',
            'extra_number' => 'п/п',
            'mark_id' => 'Марка ТС',
            'type_id' => 'Тип ТС',
            'start_at' => 'Время',
            'end_at' => 'Окончание',
            'start_str' => 'Время',
            'end_str' => 'Окончание',
            'user_id' => 'Кто записал',
            'status' => 'Статус',
            'phone' => 'Телефон',
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
     * @return ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::className(), ['id' => 'card_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMark()
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function setCard_number($value)
    {
        $this->card_number = $value;
    }

    public function getCard_number()
    {
        $card = Card::findOne($this->card_id);
        return $card ? $card->number : $this->card_id;
    }

    /**
     * @inheritdoc
     * @return \common\models\query\EntryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EntryQuery(get_called_class());
    }

    public function beforeSave($insert)
    {
        if (!empty($this->card_number)) {
            $card = Card::findOne(['number' => $this->card_number]);
            $this->card_id = $card ? $card->id : $this->card_number;
        }

        if (!empty($this->start_at)) {
            $this->day = date('d-m-Y', $this->start_at);
        }
        
        if (!empty($this->start_str)) {
            $this->start_at = \DateTime::createFromFormat('d-m-Y H:i:s', $this->day . ' ' . $this->start_str . ':00')->getTimestamp();
            if (!empty($this->end_str)) {
                $this->end_at = \DateTime::createFromFormat('d-m-Y H:i:s', $this->day . ' ' . $this->end_str . ':00')->getTimestamp();
            } else {
                $duration = $this->company->getDurationByType($this->type_id)->one();
                if (!empty($duration)) {
                    $this->end_at = $this->start_at + $duration->duration;
                } else {
                    $this->end_at = $this->start_at + $this->defaultDuration;
                }
            }
        }
        if (empty($this->start_at)) {
            $this->addError('start_at', ['Нет времени записи']);
            return false;
        }

        //номер в верхний регистр
        $this->number = mb_strtoupper(str_replace(' ', '', $this->number), 'UTF-8');
        $this->extra_number = mb_strtoupper(str_replace(' ', '', $this->extra_number), 'UTF-8');

        //подставляем тип и марку из машины, если нашли по номеру
        $car = Car::findOne(['number' => $this->number]);
        if ($car) {
            $this->mark_id = $car->mark_id;
            $this->type_id = $car->type_id;
        }

        //проверяем чтобы не пересеклось с существующими записями (только когда создаем просто запись без акта)
        /*if ($this->isNewRecord && !$this->act_id) {
            $existed = self::find()->andWhere(['company_id' => $this->company_id])->andWhere(['<', 'start_at', $this->start_at + 600])->andWhere(['>', 'end_at', $this->start_at + 600])->all();
            if (!empty($existed)) {
                $this->addError('start_at', ['Время начала совпадает с существующей записью']);
                return false;
            }
            $existed = self::find()->andWhere(['company_id' => $this->company_id])->andWhere(['<', 'start_at', $this->end_at - 600])->andWhere(['>', 'end_at', $this->end_at - 600])->all();
            if (!empty($existed)) {
                $this->addError('end_at', ['Время окончания совпадает с существующей записью']);
                return false;
            }
        }*/

        if ($insert && !empty(Yii::$app->user->identity->id)) {
            $this->user_id = Yii::$app->user->identity->id;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param $status
     * @return mixed
     */
    static function colorForStatus($status)
    {
        $paymentStatus = [
            self::STATUS_APPROVED     => 'monthly-act-success',
            self::STATUS_NOT_APPROVED => 'monthly-act-danger',
        ];

        return $paymentStatus[$status];
    }
}
