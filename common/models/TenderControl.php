<?php

namespace common\models;

use common\traits\JsonTrait;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tender_control".
 *
 * @property integer $id
 * @property integer $tender_id
 * @property integer $user_id
 * @property string $send
 * @property string $date_send
 * @property string $date_enlistment
 * @property string $site_address
 * @property string $platform
 * @property string $customer
 * @property string $purchase
 * @property string $type_payment
 * @property string $money_unblocking
 * @property string $return
 * @property string $date_return
 * @property string $comment
 * @property string $payment_status
 */
class TenderControl extends ActiveRecord
{
    use JsonTrait;

    const PAYMENT_STATUS_NOT_DONE = 0;
    const PAYMENT_STATUS_DONE = 1;
    const PAYMENT_STATUS_CASH = 2;

    public static $paymentStatus = [
        self::PAYMENT_STATUS_NOT_DONE => '-',
        self::PAYMENT_STATUS_DONE => '+',
        self::PAYMENT_STATUS_CASH => '+ -',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender_control}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_address', 'type_payment', 'payment_status', 'is_archive', 'tender_id'], 'integer'],
            [['send', 'return'], 'safe'],
            [['comment'], 'string', 'max' => 10000],
            [['date_send', 'date_enlistment', 'money_unblocking', 'date_return'], 'string', 'max' => 20],
            [['platform', 'customer', 'purchase'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tender_id' => 'ID Тендера',
            'user_id' => 'Ответственный сотрудник',
            'send' => 'Мы отправили',
            'date_send' => 'Дата отправки',
            'date_enlistment' => 'Дата зачисления',
            'site_address' => 'Адрес площадки',
            'platform' => 'Электронная площадка',
            'customer' => 'Заказчик',
            'purchase' => 'Что закупается?',
            'type_payment' => 'Тип платежа',
            'money_unblocking' => 'Дата разблокировки денег',
            'return' => 'Нам вернули',
            'date_return' => 'Фактическая дата возврата с ЭТП',
            'comment' => 'Комментарий',
            'is_archive' => 'Архив',
            'payment_status' => 'Статус',
        ];
    }

    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if($this->isNewRecord) {

            if($this->date_send) {
                $this->date_send = (String) strtotime($this->date_send);
            }
            if($this->date_enlistment) {
                $this->date_enlistment = (String) strtotime($this->date_enlistment);
            }
            if($this->money_unblocking) {
                $this->money_unblocking = (String) strtotime($this->money_unblocking);
            }
            if($this->date_return) {
                $this->date_return = (String) strtotime($this->date_return);
            }
        }
        return parent::beforeSave($insert);

    }

    static function payDis($val)
    {
        $currentUser = Yii::$app->user->identity;
        if (($val == self::PAYMENT_STATUS_DONE || $val == self::PAYMENT_STATUS_CASH) && ($currentUser) && ($currentUser->role != User::ROLE_ADMIN)) {
            $disabled = true;
        } else {
            $disabled = false;
        }
        return $disabled;
    }

    static function colorForPaymentStatus($status)
    {
        $paymentStatus = [
            self::PAYMENT_STATUS_DONE => 'btn-success',
            self::PAYMENT_STATUS_NOT_DONE => 'btn-danger',
            self::PAYMENT_STATUS_CASH => 'btn-warning',
        ];
        return $paymentStatus[$status];

    }
}
