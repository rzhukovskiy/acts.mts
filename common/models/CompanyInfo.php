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
 *
 * @property Company $company
 *
 * @property string $start_str
 * @property string $end_str
 */
class CompanyInfo extends ActiveRecord
{
    public $start_str;
    public $end_str;

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
            [['company_id', 'start_at', 'end_at'], 'integer'],
            [['phone', 'index', 'city', 'street', 'house', 'address_mail', 'email', 'start_str', 'end_str'], 'string', 'max' => 255],
        ];
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
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function beforeSave($insert)
    {
        if (!empty($this->start_str)) {
            list($hrs, $mnts) = explode(':', $this->start_str);
            $this->start_at = $hrs * 3600 + $mnts * 60;

            list($hrs, $mnts) = explode(':', $this->end_str);
            $this->end_at = $hrs * 3600 + $mnts * 60;
        }

        return parent::beforeSave($insert);
    }
}
