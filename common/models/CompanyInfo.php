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
 * @property integer $nds
 * @property string $phone
 * @property string $index
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $address_mail
 * @property string $email
 * @property string $pay
 * @property string $contract
 * @property string $inn
 * @property string $lat
 * @property string $lng
 * @property integer $contract_date
 * @property integer $comment
 *
 * @property Company $company
 *
 * @property string $contract_date_str
 */
class CompanyInfo extends ActiveRecord
{
    private $contract_date_str;
    private $fullAddress;
    public $payTypeDay;
    public $payDay;
    public $prePaid;
    private $comment;
    private $time_location;
    private $website;

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
            [['website', 'inn', 'lat', 'lng'], 'safe'],
            [['company_id', 'contract_date', 'time_location', 'nds'], 'integer'],
            ['comment', 'string', 'max' => 2500],
            [['contract_date_str', 'pay', 'contract', 'phone', 'index', 'city', 'street', 'house', 'address_mail', 'email'], 'string', 'max' => 255],        ];
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
            'address_mail' => 'Адрес для почтового отправления',
            'email' => 'Официальный адрес эл. почты',
            'pay' => 'Сроки оплаты',
            'contract' => 'Номер договора',
            'contract_date_str' => 'Дата заключения договора',
            'comment' => 'Комментарий',
            'payTypeDay' => 'Тип дней',
            'payDay' => 'Количество дней',
            'prePaid' => 'Аванс',
            'time_location' => 'Разница с Москвой',
            'website' => 'Веб-сайт',
            'nds' => 'НДС',
            'inn' => 'ИНН',
            'lat' => 'Широта',
            'lng' => 'Долгота',
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

            $arrFullAddress = [];

            if (isset($this->city)) {
                if(mb_strlen($this->city) > 0) {
                    $arrFullAddress[] = $this->city;
                }
            }

            if (isset($this->street)) {
                if(mb_strlen($this->street) > 0) {
                    $arrFullAddress[] = $this->street;
                }
            }

            if (isset($this->house)) {
                if(mb_strlen($this->house) > 0) {
                    $arrFullAddress[] = $this->house;
                }
            }

            if (isset($this->index)) {
                if(mb_strlen($this->index) > 0) {
                    $arrFullAddress[] = $this->index;
                }
            }

            $this->fullAddress = implode(', ', $arrFullAddress);

            /*$this->fullAddress = implode(', ',
                [
                    $this->city ? $this->city : $this->getAttributeLabel('city'),
                    $this->street ? $this->street : $this->getAttributeLabel('street'),
                    $this->house ? $this->house : $this->getAttributeLabel('house'),
                    $this->index ? $this->index : $this->getAttributeLabel('index'),
                ]);*/
        }

        return $this->fullAddress;
    }
    
    public function setFullAddress($value)
    {
        $this->fullAddress = $value;
    }

    // Новый вывод сроков оплаты
    public function getPayData()
    {
        $arrPayData = explode(':', $this->pay);

        if(count($arrPayData) > 1) {

            $stringRes = '';

            if($arrPayData[0] == 4) {
                $stringRes = 'Аванс ' . (isset($arrPayData[2]) ? $arrPayData[2] : 0) . ' руб.';
            } else {

                if (count($arrPayData) == 3) {
                    $stringRes .= (isset($arrPayData[2]) ? $arrPayData[2] : 0) . ' руб. + ';
                }

                if (($arrPayData[0] == 0) || ($arrPayData[0] == 2)) {
                    $stringRes .= (isset($arrPayData[1]) ? $arrPayData[1] : 30) . ' банковских дней';
                } else {
                    $stringRes .= (isset($arrPayData[1]) ? $arrPayData[1] : 30) . ' календарных дней';
                }

            }

            return $stringRes;
        } else {
            return $this->pay;
        }

    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($value)
    {
        $this->comment = $value;
    }

    public function getTime_location()
    {
        return $this->time_location;
    }

    public function setTime_location($value)
    {
        $this->time_location = $value;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($value)
    {
        $this->website = $value;
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
        if (!empty($this->contract_date_str)) {
            $this->contract_date = \DateTime::createFromFormat('d-m-Y', $this->contract_date_str)->getTimestamp();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->company->address != $this->city) {
            $this->company->address = $this->city;
            $this->company->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
