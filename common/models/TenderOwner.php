<?php

namespace common\models;

use Yii;
use common\traits\JsonTrait;

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
 * @property string $date_consideration
 * @property string $date_bidding
 * @property string $electronic_platform
 * @property string $link_official
 * @property string $customer
 * @property string $customer_full
 * @property string $fz
 * @property string $purchase_name
 * @property string $inn_customer
 * @property string $number
 * @property integer $tender_user
 * @property integer $tender_id
 * @property integer $status
 * @property float $purchase
 * @property float $request_security
 */
class TenderOwner extends \yii\db\ActiveRecord
{

    use JsonTrait;

    const STATUS_NOT = 0;
    const STATUS_LOW_PRICE = 1;
    const STATUS_LOW_CONTRACT = 2;
    const STATUS_ONE_PROVIDER = 3;
    const STATUS_NOT_TIME = 4;
    const STATUS_LOW_TIME = 5;
    const STATUS_REQUEST = 6;
    const STATUS_DONT_PURCHASE = 7;
    const STATUS_DUPLICATE = 8;
    const STATUS_NOT_PASS = 9;
    const STATUS_CASH_SEARCH = 10;
    const STATUS_NOT_HAVE_TIME = 11;
    const STATUS_OTHER = 12;

    public static $status = [
        self::STATUS_NOT => 'Выберите статус',
        self::STATUS_LOW_PRICE => 'Заниженная начальная максимальная цена',
        self::STATUS_LOW_CONTRACT => 'Низкая цена контракта',
        self::STATUS_ONE_PROVIDER => 'Закупка у единственного поставщика',
        self::STATUS_NOT_TIME => 'Позднее обнаружение закупки',
        self::STATUS_LOW_TIME => 'Короткий срок подачи',
        self::STATUS_REQUEST => 'Запрос КП для обоснования цены',
        self::STATUS_DONT_PURCHASE => 'Не наш предмет закупки',
        self::STATUS_DUPLICATE => 'Дублируется',
        self::STATUS_NOT_PASS => 'Подача на бум. носителе (не проходим по срокам подачи)',
        self::STATUS_CASH_SEARCH => 'Платная поисковая система',
        self::STATUS_NOT_HAVE_TIME => 'Не успели',
        self::STATUS_OTHER => 'Другое',
    ];

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
            [['purchase', 'request_security'], 'safe'],
            [['text', 'reason_not_take', 'customer_full', 'purchase_name'], 'string', 'max' => 5000],
            [['data', 'date_from', 'date_to', 'inn_customer', 'date_consideration', 'date_bidding'], 'string', 'max' => 20],
            [['number'], 'string', 'max' => 30],
            [['link', 'city', 'electronic_platform', 'link_official', 'customer', 'fz'], 'string', 'max' => 255],
            [['tender_user', 'tender_id', 'status'], 'integer'],
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
            'city' => 'Город, Область поставки',
            'purchase' => 'Сумма закупки',
            'date_from' => 'Начало подачи заявкик',
            'date_to' => 'Окончание подачи заявки',
            'reason_not_take' => 'Комментарий',
            'date_bidding' => 'Дата и время начала торгов',
            'date_consideration' => 'Дата и время рассмотрения заявок',
            'purchase_name' => 'Что закупают?',
            'fz' => 'Тип заявки',
            'customer' => 'Заказчик',
            'customer_full' => 'Заказчик полное',
            'inn_customer' => 'ИНН заказчика',
            'link_official' => 'Прямая ссылка',
            'request_security' => 'Обеспечение заявки',
            'electronic_platform' => 'Электронная площадка',
            'status' => 'Статус',
            'number' => 'Номер',
        ];
    }

    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if($this->isNewRecord) {
            if ($this->date_from) {
            $this->date_from = (String) strtotime($this->date_from);
            }
            if ($this->date_to) {
            $this->date_to = (String) strtotime($this->date_to);
            }
            if ($this->date_bidding) {
            $this->date_bidding = (String)strtotime($this->date_bidding);
            }
            if ($this->date_consideration) {
            $this->date_consideration = (String)strtotime($this->date_consideration);
            }
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
