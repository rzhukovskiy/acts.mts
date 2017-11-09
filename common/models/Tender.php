<?php

namespace common\models;

use yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "tender".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $date_search
 * @property string $city
 * @property string $place
 * @property string $number_purchase
 * @property string $customer
 * @property string $service_type
 * @property float $price_nds
 * @property float $pre_income
 * @property float $final_price
 * @property integer $percent_down
 * @property integer $percent_max
 * @property integer $federal_law
 * @property integer $method_purchase
 * @property float $contract_security
 * @property integer $status_request_security
 * @property string $date_status_request
 * @property integer $status_contract_security
 * @property string $date_status_contract
 * @property string $notice_eis
 * @property integer $key_type
 * @property string $competitor
 * @property string $date_request_start
 * @property string $date_request_end
 * @property string $time_request_process
 * @property string $time_bidding_start
 * @property string $time_bidding_end
 * @property string $date_contract
 * @property string $term_contract
 * @property string $comment
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $purchase_status
 * @property string $comment_status_proc
 * @property string $user_id
 * @property string $comment_customer
 * @property string $inn_customer
 * @property string $contacts_resp_customer
 * @property float $maximum_purchase_price
 * @property float $cost_purchase_completion
 * @property float $maximum_purchase_nds
 * @property float $maximum_purchase_notnds
 * @property float $maximum_agreed_calcnds
 * @property float $maximum_agreed_calcnotnds
 * @property float $site_fee_participation
 * @property float $ensuring_application
 * @property string $inn_competitors
 * @property string $comment_date_contract
 */
class Tender extends \yii\db\ActiveRecord
{
    private $purchase_status;
    private $comment_status_proc;
    private $user_id;
    private $comment_customer;
    private $inn_customer;
    private $contacts_resp_customer;
    private $maximum_purchase_price;
    private $cost_purchase_completion;
    private $maximum_purchase_nds;
    private $maximum_purchase_notnds;
    private $maximum_agreed_calcnds;
    private $maximum_agreed_calcnotnds;
    private $site_fee_participation;
    private $ensuring_application;
    private $inn_competitors;
    private $comment_date_contract;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender}}';
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
            [['company_id'], 'required'],
            [['company_id', 'purchase_status', 'percent_down', 'percent_max', 'federal_law', 'method_purchase', 'status_request_security', 'status_contract_security', 'key_type'], 'integer'],
            [['price_nds', 'pre_income', 'final_price', 'contract_security', 'maximum_purchase_price', 'cost_purchase_completion', 'maximum_purchase_nds', 'maximum_purchase_notnds', 'maximum_agreed_calcnds', '$maximum_agreed_calcnotnds', 'site_fee_participation', 'ensuring_application', 'service_type', 'user_id'], 'safe'],
            [['date_search', 'date_status_request', 'date_status_contract', 'date_request_start', 'date_request_end', 'time_request_process', 'time_bidding_start', 'time_bidding_end', 'date_contract', 'term_contract'], 'string', 'max' => 20],
            [['city', 'place', 'number_purchase', 'customer', 'competitor'], 'string', 'max' => 255],
            [['notice_eis'], 'string', 'max' => 100],
            [['inn_customer'], 'string', 'max' => 200],
            [['comment', 'comment_status_proc', 'comment_date_contract', 'comment_customer', 'contacts_resp_customer', 'inn_competitors'], 'string', 'max' => 10000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Компания',
            'date_search' => 'Дата нахождения закупки',
            'city' => 'Город, Область поставки',
            'place' => 'Электронная площадка',
            'number_purchase' => 'Номер закупки на площадке',
            'customer' => 'Заказчик',
            'service_type' => 'Закупаемые услуги, товары',
            'price_nds' => 'Максимальная стоимость закупки',
            'pre_income' => 'Предварительная прибыль от закупки',
            'final_price' => 'Стоимость закупки по завершению закупки с НДС',
            'percent_down' => 'Процентное снижение по завершению закупки в процентах',
            'percent_max' => 'Максимальное согласованное расчетное снижение в процентах',
            'federal_law' => 'ФЗ',
            'method_purchase' => 'Способ закупки',
            'contract_security' => 'Обеспечение контракта',
            'status_request_security' => 'Статус обеспечения заявки',
            'date_status_request' => 'Дата изменения статуса заявки',
            'status_contract_security' => 'Статус обеспечения контракта',
            'date_status_contract' => 'Дата изменения статуса контракта',
            'notice_eis' => 'Номер извещения в ЕИС',
            'key_type' => 'Тип ключа',
            'competitor' => 'Потенциальные конкуренты',
            'date_request_start' => 'Начало подачи заявки',
            'date_request_end' => 'Окончание подачи заявки',
            'time_request_process' => 'Дата и время рассмотрения заявок',
            'time_bidding_start' => 'Дата и время начала торгов',
            'time_bidding_end' => 'Дата и время подведения итогов',
            'date_contract' => 'Дата заключения договора',
            'term_contract' => 'Дата окончания заключенного договора',
            'comment' => 'Комментарий',
            'purchase_status' => 'Статус закупки',
            'comment_status_proc' => 'Комментарий к статусу закупки',
            'user_id' => 'Сотрудник',
            'comment_customer' => 'Комментарий к полю "Заказчик"',
            'inn_customer' => 'ИНН заказчика',
            'contacts_resp_customer' => 'Контакты ответственных лиц заказчика',
            'maximum_purchase_price' => 'Максимальная начальная стоимость закупки без НДС',
            'cost_purchase_completion' => 'Стоимость закупки по завершению закупки без НДС',
            'maximum_purchase_nds' => 'Снижение от максимальной начальной стоимости закупки по завершению закупки в рублях с НДС',
            'maximum_purchase_notnds' => 'Снижение от максимальной начальной стоимости закупки по завершению закупки в рублях без НДС',
            'maximum_agreed_calcnds' => 'Максимальное согласованное расчетное снижение в рублях с НДС',
            'maximum_agreed_calcnotnds' => 'Максимальное согласованное расчетное снижение в рублях без НДС',
            'site_fee_participation' => 'Плата площадке за участие',
            'ensuring_application' => 'Обеспечение заявки',
            'inn_competitors' => 'ИНН конкурентов',
            'comment_date_contract' => 'Комментарий к сроку договора',
        ];
    }

    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if($this->isNewRecord) {
            // переводим дату в нужный формат
            $this->date_search = (String) strtotime($this->date_search);
            $this->date_status_request = (String) strtotime($this->date_status_request);
            $this->date_status_contract = (String) strtotime($this->date_status_contract);
            $this->date_request_start = (String) strtotime($this->date_request_start);
            $this->date_request_end = (String) strtotime($this->date_request_end);
            $this->time_request_process = (String) strtotime($this->time_request_process);
            $this->time_bidding_start = (String) strtotime($this->time_bidding_start);
            $this->time_bidding_end = (String) strtotime($this->time_bidding_end);

            if($this->date_contract) {
                $this->date_contract = (String) strtotime($this->date_contract);
            }
            if($this->term_contract) {
                $this->term_contract = (String) strtotime($this->term_contract);
            }

            // запись в базу нескольких услуг
            if (is_array($this->service_type)) {

                $arrServices = $this->service_type;

                if (count($arrServices) > 0) {
                    $stringServices = '';

                    for ($i = 0; $i < count($arrServices); $i++) {
                        if ($i == 0) {
                            $stringServices .= $arrServices[$i];
                        } else {
                            $stringServices .= ', ' . $arrServices[$i];
                        }
                    }

                    $this->service_type = $stringServices;

                }

            }
        }

        return parent::beforeSave($insert);
    }

}
