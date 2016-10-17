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
 * @property string $img
 * @property integer $created_at
 * @property integer $updated_at
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
        self::PAYMENT_STATUS_NOT_DONE => [
            'ru' => 'Не оплачен',
            'en' => 'not paid',
        ],
        self::PAYMENT_STATUS_DONE     => [
            'ru' => 'Оплачен',
            'en' => 'paid',
        ]
    ];

    public static $actStatus = [
        self::ACT_STATUS_NOT_SIGNED  => [
            'ru' => 'Не подписан',
            'en' => 'not signed',
        ],
        self::ACT_STATUS_SEND_SCAN   => [
            'ru' => 'Отправлен скан',
            'en' => 'send scan',
        ],
        self::ACT_STATUS_SEND_ORIGIN => [
            'ru' => 'Отправлен оригинал',
            'en' => 'send original',
        ],
        self::ACT_STATUS_SIGNED_SCAN => [
            'ru' => 'Подписан скан',
            'en' => 'signed scan',
        ],
        self::ACT_STATUS_DONE        => [
            'ru' => 'Исполнен',
            'en' => 'done',
        ]
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
            [['client_id', 'type_id'], 'required'],
            [
                [
                    'client_id',
                    'type_id',
                    'profit',
                    'payment_status',
                    'payment_date',
                    'act_status',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [['img'], 'string'],
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
            'id'             => 'ID',
            'client_id'      => 'Client ID',
            'type_id'        => 'Type ID',
            'profit'         => 'Profit',
            'payment_status' => 'Payment Status',
            'payment_date'   => 'Payment Date',
            'act_status'     => 'Act Status',
            'img'            => 'Img',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
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

    public function beforeSave($insert)
    {

        if ($this->isNewRecord) {
            $this->payment_status = self::PAYMENT_STATUS_NOT_DONE;
            $this->act_status = self::ACT_STATUS_NOT_SIGNED;
        }

        return parent::beforeSave($insert);
    }

}
