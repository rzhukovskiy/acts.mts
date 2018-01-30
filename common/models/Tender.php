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
 * @property string $city
 * @property string $place
 * @property string $number_purchase
 * @property string $customer
 * @property string $service_type
 * @property float $price_nds
 * @property float $final_price
 * @property integer $federal_law
 * @property integer $method_purchase
 * @property integer $status_request_security
 * @property string $date_status_request
 * @property integer $status_contract_security
 * @property string $date_status_contract
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
 * @property string $user_id
 * @property string $work_user_id
 * @property string $work_user_time
 * @property string $comment_customer
 * @property string $inn_customer
 * @property string $contacts_resp_customer
 * @property string $comment_date_contract
 * @property integer $tender_close
 * @property string $site
 */
class Tender extends ActiveRecord
{
    private $purchase_status;
    private $comment_customer;
    private $inn_customer;
    private $contacts_resp_customer;
    private $comment_date_contract;
    private $tender_close;
    private $site;
    public $curentTender;

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
            [['company_id', 'purchase_status', 'work_user_id', 'site_address'], 'integer'],
            [['price_nds', 'final_price', 'service_type', 'user_id', 'federal_law', 'method_purchase', 'status_request_security', 'status_contract_security', 'tender_close'], 'safe'],
            [['date_status_request', 'date_status_contract', 'date_request_start', 'date_request_end', 'time_request_process', 'time_bidding_start', 'time_bidding_end', 'date_contract', 'term_contract', 'work_user_time'], 'string', 'max' => 20],
            [['city', 'place', 'number_purchase', 'customer'], 'string', 'max' => 255],
            [['inn_customer', 'site'], 'string', 'max' => 200],
            [['purchase'], 'string', 'max' => 255],
            [['comment', 'comment_date_contract', 'comment_customer', 'contacts_resp_customer'], 'string', 'max' => 10000],
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
            'city' => 'Город, Область поставки',
            'place' => 'Электронная площадка',
            'number_purchase' => 'Номер закупки на площадке',
            'customer' => 'Заказчик',
            'service_type' => 'Закупаемые услуги, товары',
            'price_nds' => 'Максимальная стоимость закупки с НДС',
            'final_price' => 'Стоимость закупки по завершению закупки с НДС',
            'federal_law' => 'ФЗ',
            'method_purchase' => 'Способ закупки',
            'status_request_security' => 'Статус обеспечения заявки',
            'date_status_request' => 'Дата изменения статуса заявки',
            'status_contract_security' => 'Статус обеспечения контракта',
            'date_status_contract' => 'Дата изменения статуса контракта',
            'date_request_start' => 'Начало подачи заявки',
            'date_request_end' => 'Окончание подачи заявки',
            'time_request_process' => 'Дата и время рассмотрения заявок',
            'time_bidding_start' => 'Дата и время начала торгов',
            'time_bidding_end' => 'Дата и время подведения итогов',
            'date_contract' => 'Дата заключения договора',
            'term_contract' => 'Дата окончания заключенного договора',
            'comment' => 'Общий комментарий',
            'purchase_status' => 'Статус закупки',
            'user_id' => 'Ответственный сотрудник',
            'work_user_id' => 'Разработка тех. задания',
            'work_user_time' => 'Дата добавления разработчика тех. задания',
            'comment_customer' => 'Комментарий к полю "Заказчик"',
            'inn_customer' => 'ИНН заказчика',
            'contacts_resp_customer' => 'Контакты ответственных лиц заказчика',
            'comment_date_contract' => 'Комментарий к сроку договора',
            'tender_close' => 'Закрыть закупку',
            'files' => 'Вложения',
            'site' => 'Прямая ссылка',
            'companyname' => 'Имя компании',
            'site_address' => 'Адрес площадки',
            'purchase' => 'Что закупается?',
            'type_payment' => 'Тип платежа',

        ];
    }

    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if($this->isNewRecord) {
            // переводим дату в нужный формат
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

        }

        // Записываем дату изменения разработчика технического задания
        $this->curentTender = self::findOne($this->id);
        if($this->work_user_id ) {

            if(isset($this->curentTender->work_user_id)) {
                if($this->curentTender->work_user_id != $this->work_user_id) {
                    $this->work_user_time = (String) time();
                }
            } else {
                $this->work_user_time = (String) time();
            }

        } else {
            $this->work_user_time = NULL;
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

    public function getComment_date_contract()
    {
        return $this->comment_date_contract;
    }

    public function setComment_date_contract($value)
    {
        $this->comment_date_contract = $value;
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
}
