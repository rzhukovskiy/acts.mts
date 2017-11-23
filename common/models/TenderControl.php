<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tender_control".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $send
 * @property string $date_send
 * @property string $date_enlistment
 * @property string $site_address
 * @property string $platform
 * @property string $customer
 * @property string $purchase
 * @property string $eis_platform
 * @property string $type_payment
 * @property string $money_unblocking
 * @property string $return
 * @property string $date_return
 * @property string $balance_work
 * @property string $comment
 */
class TenderControl extends ActiveRecord
{
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
            [['user_id', 'site_address', 'type_payment', 'is_archive'], 'integer'],
            [['send', 'return', 'balance_work'], 'safe'],
            [['comment'], 'string', 'max' => 10000],
            [['date_send', 'date_enlistment', 'money_unblocking', 'date_return'], 'string', 'max' => 20],
            [['platform', 'customer', 'purchase', 'eis_platform'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Сотрудник',
            'send' => 'Отправили',
            'date_send' => 'Дата отправки',
            'date_enlistment' => 'Дата зачисления',
            'site_address' => 'Адрес площадки',
            'platform' => 'Площадка',
            'customer' => 'Заказчик',
            'purchase' => 'Закупка',
            'eis_platform' => '№ ЕИС на площадке',
            'type_payment' => 'Тип платежа',
            'money_unblocking' => 'Дата разблокировки денег',
            'return' => 'Возврат',
            'date_return' => 'Дата возврата',
            'balance_work' => 'Остаток в работе',
            'comment' => 'Комментарий',
            'is_archive' => 'Архив'
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
}