<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "monthly_act".
 *
 * @property integer $id
 * @property string $client_id
 * @property string $type_id
 * @property integer $profit
 * @property integer $payment_status
 * @property integer $payment_date
 * @property integer $act_status
 * @property string $act_date
 * @property string $img
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property string $act_comment
 * @property string $act_send_date
 * @property string $act_client_get_date
 * @property string $act_we_get_date
 * @property string $payment_comment
 * @property string $payment_estimate_date
 *
 * @property Company $client
 */
class MonthlyAct extends \yii\db\ActiveRecord
{
    const PAYMENT_STATUS_NOT_DONE = 0;
    const PAYMENT_STATUS_DONE = 1;

    const ACT_STATUS_NOT_SIGNED = 0;
    const ACT_STATUS_SEND_SCAN = 1;
    const ACT_STATUS_SEND_ORIGIN = 2;
    const ACT_STATUS_SIGNED_SCAN = 3;
    const ACT_STATUS_DONE = 4;


    public static $paymentStatus = [
        self::PAYMENT_STATUS_NOT_DONE => 'Не оплачен',
        self::PAYMENT_STATUS_DONE     => 'Оплачен'
    ];

    public static $actStatus = [
        self::ACT_STATUS_NOT_SIGNED  => 'Не подписан',
        self::ACT_STATUS_SEND_SCAN   => 'Отправлен скан',
        self::ACT_STATUS_SEND_ORIGIN => 'Отправлен оригинал',
        self::ACT_STATUS_SIGNED_SCAN => 'Подписан скан',
        self::ACT_STATUS_DONE        => 'Исполнен'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monthly_act';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'type_id', 'act_date'], 'required', 'on' => 'default'],
            [
                [
                    'client_id',
                    'type_id',
                    'profit',
                    'payment_status',
                    'act_status',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [['img', 'act_date', 'payment_date'], 'string'],
            [
                [
                    'act_comment',
                    'act_send_date',
                    'act_client_get_date',
                    'act_we_get_date',
                    'payment_comment',
                    'payment_estimate_date'
                ],
                'string',
                'on' => 'detail'
            ],
            [
                ['client_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['client_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'client_id'             => 'Клиент',
            'type_id'               => 'Тип',
            'profit'                => 'Доход',
            'payment_status'        => 'Статус оплаты',
            'payment_date'          => 'Дата оплаты',
            'act_status'            => 'Статус акта',
            'act_date'              => 'Дата акта',
            'img'                   => 'Изображения',
            'act_comment'           => 'Комментарии к акту',
            'act_send_date'         => 'Дата отправления акта по почте',
            'act_client_get_date'   => 'Предполагаемая дата получения клиентом',
            'act_we_get_date'       => 'Предполагаемая дата получения акта нами',
            'payment_comment'       => 'Комментарии к оплате',
            'payment_estimate_date' => 'Дата предполагаемой оплаты',
            'created_at'            => 'Created At',
            'updated_at'            => 'Updated At',
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
     * @param $attr
     */
    public function timestampToDate($attr)
    {
        if (isset($this->$attr)) {
            $this->$attr = (new \DateTime())->setTimestamp($this->$attr)->format('d-m-Y');
        }
    }

    /**
     * @param $attr
     */
    public function dateToTimestamp($attr)
    {
        if (isset($this->$attr)) {
            $this->$attr = \DateTime::createFromFormat('d-m-Y H:i:s', $this->$attr . ' 12:00:00')->getTimestamp();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Company::className(), ['id' => 'client_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\MonthlyActQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\MonthlyActQuery(get_called_class());
    }

    public function afterFind()
    {
        $this->timestampToDate('payment_date');
        $this->timestampToDate('act_send_date');
        $this->timestampToDate('act_client_get_date');
        $this->timestampToDate('act_we_get_date');
        $this->timestampToDate('payment_estimate_date');

        parent::afterFind();
    }

    public function beforeSave($insert)
    {

        if ($this->isNewRecord) {
            $this->payment_status = self::PAYMENT_STATUS_NOT_DONE;
            $this->act_status = self::ACT_STATUS_NOT_SIGNED;
        }

        $this->dateToTimestamp('payment_date');
        $this->dateToTimestamp('act_send_date');
        $this->dateToTimestamp('act_client_get_date');
        $this->dateToTimestamp('act_we_get_date');
        $this->dateToTimestamp('payment_estimate_date');

        if (!empty($this->payment_date) && $this->payment_status == MonthlyAct::PAYMENT_STATUS_NOT_DONE) {
            $this->payment_status = MonthlyAct::PAYMENT_STATUS_DONE;
        }

        return parent::beforeSave($insert);
    }

}
