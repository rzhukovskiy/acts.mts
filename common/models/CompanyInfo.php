<?php

namespace common\models;

use common\models\query\CompanyInfoQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_info}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $phone
 * @property string $index
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $address_mail
 * @property string $email
 * @property integer $start_at
 * @property integer $end_at
 * @property string $pay
 * @property string $contract
 * @property integer $contract_date
 *
 * @property Company $company
 *
 * @property string $start_str
 * @property string $end_str
 * @property string $contract_date_str
 */
class CompanyInfo extends ActiveRecord
{
    public $start_str;
    public $end_str;

    private $contract_date_str;
    private $fullAddress;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['company_id', 'start_at', 'end_at', 'contract_date'], 'integer'],
            [['contract_date_str', 'pay', 'contract', 'phone', 'index', 'city', 'street', 'house', 'address_mail', 'email', 'start_str', 'end_str'], 'string', 'max' => 255],        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Телефон',
            'index' => 'Индекс',
            'city' => 'Город',
            'street' => 'Улица',
            'house' => 'Строение',
            'address_mail' => 'Почтовый адрес',
            'email' => 'Имейл',
            'pay' => 'Дни оплаты',
            'contract' => 'Договор',
            'contract_date_str' => 'Дата договора',
            'start_at' => 'Начало работы',
            'end_at' => 'Окончание работы',
            'start_str' => 'Начало работы',
            'end_str' => 'Окончание работы',
        ];
    }

    /**
     * @inheritdoc
     * @return CompanyInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CompanyInfoQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        if (!$this->fullAddress) {
            $this->fullAddress = implode(', ',
                [
                    $this->city,
                    $this->street,
                    $this->house,
                    $this->index,
                ]);
        }

        return $this->fullAddress;
    }
    
    public function setFullAddress($value)
    {
        $this->fullAddress = $value;
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getContract_date_str()
    {
        return $this->contract_date ? date('d-m-Y', $this->contract_date) : '';
    }

    public function setContract_date_str($value)
    {
        $this->contract_date_str = $value;
    }

    public function beforeSave($insert)
    {
        if (!empty($this->start_str)) {
            list($hrs, $mnts) = explode(':', $this->start_str);
            $this->start_at = $hrs * 3600 + $mnts * 60;
        }
        if (!empty($this->end_str)) {
            list($hrs, $mnts) = explode(':', $this->end_str);
            $this->end_at = $hrs * 3600 + $mnts * 60;
        }

        if (!empty($this->contract_date_str)) {
            $this->contract_date = \DateTime::createFromFormat('d-m-Y', $this->contract_date_str)->getTimestamp();
        }

        return parent::beforeSave($insert);
    }
}
