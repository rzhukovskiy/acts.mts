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
 * @property integer $price_nds
 * @property integer $pre_income
 * @property integer $first_price
 * @property integer $final_price
 * @property integer $percent_down
 * @property integer $percent_max
 * @property integer $federal_law
 * @property integer $method_purchase
 * @property integer $contract_security
 * @property integer $participate_price
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
 */
class Tender extends \yii\db\ActiveRecord
{
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
            [['company_id', 'city', 'place', 'number_purchase', 'customer', 'service_type', 'price_nds', 'pre_income', 'first_price', 'percent_down', 'percent_max', 'federal_law', 'method_purchase', 'status_request_security', 'status_contract_security', 'notice_eis', 'date_request_start', 'date_request_end', 'time_request_process', 'time_bidding_start', 'time_bidding_end', 'date_contract', 'term_contract'], 'required'],
            [['company_id', 'price_nds', 'pre_income', 'first_price', 'final_price', 'percent_down', 'percent_max', 'federal_law', 'method_purchase', 'contract_security', 'participate_price', 'status_request_security', 'status_contract_security', 'key_type'], 'integer'],
            [['date_search', 'date_status_request', 'date_status_contract', 'date_request_start', 'date_request_end', 'time_request_process', 'time_bidding_start', 'time_bidding_end', 'date_contract', 'term_contract'], 'string', 'max' => 20],
            [['city', 'place', 'number_purchase', 'customer', 'competitor'], 'string', 'max' => 255],
            [['service_type'], 'safe'],
            [['notice_eis'], 'string', 'max' => 100],
            ['comment', 'string', 'max' => 5000],
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
            'date_search' => 'Дата находки тендера',
            'city' => 'Город',
            'place' => 'Электронаая площадка',
            'number_purchase' => 'Номер закупки',
            'customer' => 'Заказчик',
            'service_type' => 'Закупаемы услуги',
            'price_nds' => 'Цена с НДС',
            'pre_income' => 'Предварительная прибыль от контраката',
            'first_price' => 'Первоначальная цена контракта',
            'final_price' => 'Окончательная цена контракта',
            'percent_down' => 'Процентное снижение',
            'percent_max' => 'Максимальный процент снижения',
            'federal_law' => 'ФЗ',
            'method_purchase' => 'Способ закупки',
            'contract_security' => 'Обеспечение контракта',
            'participate_price' => 'Стоимость участия в торгах',
            'status_request_security' => 'Статус обеспечения заявки',
            'date_status_request' => 'Дата изменения статуса заявки',
            'status_contract_security' => 'Статус обеспечения контракта',
            'date_status_contract' => 'Дата изменения статуса контракта',
            'notice_eis' => 'Номер извещения в ЕИС',
            'key_type' => 'Тип ключа',
            'competitor' => 'Потенциальный конкурент',
            'date_request_start' => 'Начало подачи заявки',
            'date_request_end' => 'Окончание подачи заявки',
            'time_request_process' => 'Дата и время рассмотрения заявок',
            'time_bidding_start' => 'Дата и время начала торгов',
            'time_bidding_end' => 'Дата и время подведения торгов',
            'date_contract' => 'Дата заключения договора',
            'term_contract' => 'Срок договора',
            'comment' => 'Комментарий',
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
            $this->date_contract = (String) strtotime($this->date_contract);
            $this->term_contract = (String) strtotime($this->term_contract);

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
