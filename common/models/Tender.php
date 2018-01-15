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
 * @property string $comment_date_contract
 * @property integer $tender_close
 * @property string $site
 * @property float $last_sentence_nds
 * @property float $last_sentence_nonds
 */
class Tender extends ActiveRecord
{
    private $purchase_status;
    private $comment_status_proc;
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
    private $comment_date_contract;
    private $tender_close;
    private $site;
    private $last_sentence_nds;
    private $last_sentence_nonds;

    /**
     * @var UploadedFile
     */
    public $files;

    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год'];

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
            [['company_id', 'purchase_status', 'percent_down', 'percent_max'], 'integer'],
            [['price_nds', 'pre_income', 'final_price', 'contract_security', 'maximum_purchase_price', 'cost_purchase_completion', 'maximum_purchase_nds', 'maximum_purchase_notnds', 'maximum_agreed_calcnds', 'maximum_agreed_calcnotnds', 'site_fee_participation', 'ensuring_application', 'service_type', 'user_id', 'federal_law', 'method_purchase', 'key_type', 'status_request_security', 'status_contract_security', 'tender_close', 'last_sentence_nds', 'last_sentence_nonds'], 'safe'],
            [['date_search', 'date_status_request', 'date_status_contract', 'date_request_start', 'date_request_end', 'time_request_process', 'time_bidding_start', 'time_bidding_end', 'date_contract', 'term_contract'], 'string', 'max' => 20],
            [['city', 'place', 'number_purchase', 'customer'], 'string', 'max' => 255],
            [['notice_eis'], 'string', 'max' => 100],
            [['inn_customer', 'site'], 'string', 'max' => 200],
            [['comment', 'comment_status_proc', 'comment_date_contract', 'comment_customer', 'contacts_resp_customer'], 'string', 'max' => 10000],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 30],
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
            'price_nds' => 'Максимальная стоимость закупки с НДС',
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
            'user_id' => 'Ответственный сотрудник',
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
            'comment_date_contract' => 'Комментарий к сроку договора',
            'tender_close' => 'Закрыть закупку',
            'files' => 'Вложения',
            'site' => 'Прямая ссылка',
            'companyname' => 'Имя компании',
            'last_sentence_nds' => 'Наше последнее предложение с НДС',
            'last_sentence_nonds' => 'Наше последнее предложение без НДС',
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
            // запись в базу нескольких сотрудников
            if (is_array($this->user_id)) {

                $arrUserTend = $this->user_id;

                if (count($arrUserTend) > 0) {
                    $stringUserTend = '';

                    for ($i = 0; $i < count($arrUserTend); $i++) {
                        if ($i == 0) {
                            $stringUserTend .= $arrUserTend[$i];
                        } else {
                            $stringUserTend .= ', ' . $arrUserTend[$i];
                        }
                    }

                    $this->user_id = $stringUserTend;

                }

            }
            // запись в базу нескольких способов закупки
            if (is_array($this->method_purchase)) {

                $arrMethodsTend = $this->method_purchase;

                if (count($arrMethodsTend) > 0) {
                    $stringMethods = '';

                    for ($i = 0; $i < count($arrMethodsTend); $i++) {
                        if ($i == 0) {
                            $stringMethods .= $arrMethodsTend[$i];
                        } else {
                            $stringMethods .= ', ' . $arrMethodsTend[$i];
                        }
                    }

                    $this->method_purchase = $stringMethods;

                }

            }
            // запись в базу нескольких фз
            if (is_array($this->federal_law)) {

                $arrFZ = $this->federal_law;

                if (count($arrFZ) > 0) {
                    $stringFZ = '';

                    for ($i = 0; $i < count($arrFZ); $i++) {
                        if ($i == 0) {
                            $stringFZ .= $arrFZ[$i];
                        } else {
                            $stringFZ .= ', ' . $arrFZ[$i];
                        }
                    }

                    $this->federal_law = $stringFZ;

                }

            }
            // запись в базу нескольких типов ключа
            if (is_array($this->key_type)) {

                $arrKeyType = $this->key_type;

                if (count($arrKeyType) > 0) {
                    $stringKeyType = '';

                    for ($i = 0; $i < count($arrKeyType); $i++) {
                        if ($i == 0) {
                            $stringKeyType .= $arrKeyType[$i];
                        } else {
                            $stringKeyType .= ', ' . $arrKeyType[$i];
                        }
                    }

                    $this->key_type = $stringKeyType;

                }

            }
            // запись в базу нескольких Статус обеспечения заявки
            if (is_array($this->status_request_security)) {

                $arrStatusRequest = $this->status_request_security;

                if (count($arrStatusRequest) > 0) {
                    $stringStatusRequest = '';

                    for ($i = 0; $i < count($arrStatusRequest); $i++) {
                        if ($i == 0) {
                            $stringStatusRequest .= $arrStatusRequest[$i];
                        } else {
                            $stringStatusRequest .= ', ' . $arrStatusRequest[$i];
                        }
                    }

                    $this->status_request_security = $stringStatusRequest;

                }

            }
            // запись в базу нескольких Статус обеспечения контракта
            if (is_array($this->status_contract_security)) {

                $arrСontractRequest = $this->status_contract_security;

                if (count($arrСontractRequest) > 0) {
                    $stringСontractRequest = '';

                    for ($i = 0; $i < count($arrСontractRequest); $i++) {
                        if ($i == 0) {
                            $stringСontractRequest .= $arrСontractRequest[$i];
                        } else {
                            $stringСontractRequest .= ', ' . $arrСontractRequest[$i];
                        }
                    }

                    $this->status_contract_security = $stringСontractRequest;

                }

            }
        }
        return parent::beforeSave($insert);

    }

    public function upload()
    {
        $filePath = \Yii::getAlias('@webroot/files/tenders/' . $this->id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/tenders/'))) {
            mkdir(\Yii::getAlias('@webroot/files/tenders/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/tenders/' . $this->id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/tenders/' . $this->id . '/'), 0775);
        }

        foreach ($this->files as $file) {

                if (!file_exists($filePath . $file->baseName . '.' . $file->extension)) {
                    $file->saveAs($filePath . $file->baseName . '.' . $file->extension);
                } else {

                    $filename = $filePath . $file->baseName . '.' . $file->extension;
                    $i = 1;

                    while (file_exists($filename)) {
                        $filename = $filePath . $file->baseName . '(' . $i . ').' . $file->extension;
                        $i++;
                    }

                    $file->saveAs($filename);

                }

        }

    }

    /* Связь с моделью Company*/

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /* Геттер для названия company */
    public function getCompanyName()
    {
        return $this->company->name;
    }

    public function getTendermembers()
    {
        return $this->hasOne(TenderMembers::className(), ['tender_id' => 'id']);
    }

    public function getPurchase_status()
    {
    return $this->purchase_status;
    }

    public function setPurchase_status($value)
    {
        $this->purchase_status = $value;
    }
    public function getComment_status_proc()
    {
        return $this->comment_status_proc;
    }

    public function setComment_status_proc($value)
    {
        $this->comment_status_proc = $value;
    }

    public function getComment_customer()
    {
        return $this->comment_customer;
    }

    public function setComment_customer($value)
    {
        $this->comment_customer = $value;
    }
    public function getInn_customer()
    {
        return $this->inn_customer;
    }

    public function setInn_customer($value)
    {
        $this->inn_customer = $value;
    }
    public function getContacts_resp_customer()
    {
        return $this->contacts_resp_customer;
    }

    public function setContacts_resp_customer($value)
    {
        $this->contacts_resp_customer = $value;
    }
    public function getMaximum_purchase_price()
    {
        return $this->maximum_purchase_price;
    }

    public function setMaximum_purchase_price($value)
    {
        $this->maximum_purchase_price = $value;
    }
    public function getCost_purchase_completion()
    {
        return $this->cost_purchase_completion;
    }

    public function setCost_purchase_completion($value)
    {
        $this->cost_purchase_completion = $value;
    }
    public function getMaximum_purchase_nds()
    {
        return $this->maximum_purchase_nds;
    }

    public function setMaximum_purchase_nds($value)
    {
        $this->maximum_purchase_nds = $value;
    }
    public function getNdsmaximum_purchase_not()
    {
        return $this->ndsmaximum_purchase_not;
    }

    public function setNdsmaximum_purchase_not($value)
    {
        $this->ndsmaximum_purchase_not = $value;
    }
    public function getMaximum_agreed_calcnds()
    {
        return $this->maximum_agreed_calcnds;
    }

    public function setMaximum_agreed_calcnds($value)
    {
        $this->maximum_agreed_calcnds = $value;
    }
    public function getMaximum_agreed_calcnotnds()
    {
        return $this->maximum_agreed_calcnotnds;
    }

    public function setMaximum_agreed_calcnotnds($value)
    {
        $this->maximum_agreed_calcnotnds = $value;
    }
    public function getSite_fee_participation()
    {
        return $this->site_fee_participation;
    }

    public function setSite_fee_participation($value)
    {
        $this->site_fee_participation = $value;
    }
    public function getEnsuring_application()
    {
        return $this->ensuring_application;
    }

    public function setEnsuring_application($value)
    {
        $this->ensuring_application = $value;
    }

    public function getComment_date_contract()
    {
        return $this->comment_date_contract;
    }

    public function setComment_date_contract($value)
    {
        $this->comment_date_contract = $value;
    }
    public function setMaximum_purchase_notnds($value)
    {
        $this->maximum_purchase_notnds = $value;
    }
    public function getMaximum_purchase_notnds()
    {
        return $this->maximum_purchase_notnds;
    }
    public function getTender_close()
    {
        return $this->tender_close;
    }
    public function setTender_close($value)
    {
        $this->tender_close = $value;
    }
    public function getSite()
    {
        return $this->site;
    }
    public function setSite($value)
    {
        $this->site = $value;
    }
    public function getLast_sentence_nds()
    {
        return $this->last_sentence_nds;
    }
    public function setLast_sentence_nds($value)
    {
        $this->last_sentence_nds = $value;
    }
    public function getLast_sentence_nonds()
    {
        return $this->last_sentence_nonds;
    }
    public function setgetLast_sentence_nonds($value)
    {
        $this->last_sentence_nonds = $value;
    }
}
