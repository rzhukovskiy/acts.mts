<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace frontend\models\forms;

use common\models\CompanyAttributes;
use common\models\CompanyInfo;
use Yii;
use yii\base\Model;

class OwnerForm extends Model
{

    public $name;
    public $company;
    public $email;
    public $phone;
    public $town;
    public $car_mark;
    public $car_type;
    public $car_count;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'company',
                    'email',
                    'phone',
                    'town',
                ],
                'required'
            ],
            [['car_mark', 'car_type', 'car_count',], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'name'      => 'ФИО',
            'company'   => 'Компания',
            'email'     => 'E-mail',
            'phone'     => 'Телефон',
            'town'      => 'Города',
            'car_mark'  => 'Марка ТС',
            'car_type'  => 'Вид ТС',
            'car_count' => 'Количество'
        ];
    }

    /**
     * @return array
     */
    public function getPreparedCity()
    {
        return explode(",", $this->town);
    }

    /**
     * @return array
     */
    public function getCarComplexField()
    {
        $complexField = [];
        foreach ($this->car_type as $key => $car_type) {
            if (!empty($car_type)) {
                $complexField[] = [
                    'car_mark'  => $this->car_mark[$key],
                    'car_type'  => $car_type,
                    'car_count' => $this->car_count[$key]
                ];
            }
        }

        return $complexField;
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveCompanyInfo($idCompany)
    {
        $companyInfo = new CompanyInfo();
        $companyInfo->company_id = $idCompany;
        $companyInfo->phone = $this->phone;
        $companyInfo->email = $this->email;

        return $companyInfo->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveCarAttribute($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_OWNER_CAR;
        $companyAttribute->value = $this->getCarComplexField();
        $companyAttribute->save();

        return $companyAttribute->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveTownAttribute($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_OWNER_CITY;
        $companyAttribute->value = $this->getPreparedCity();

        return $companyAttribute->save();
    }
}