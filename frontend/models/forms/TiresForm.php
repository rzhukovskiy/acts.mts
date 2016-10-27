<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace frontend\models\forms;

use common\models\CompanyAttributes;
use common\models\CompanyClient;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use Yii;
use yii\base\Model;

class TiresForm extends Model
{

    public $name;
    public $index;
    public $city;
    public $street;
    public $building;
    public $phone;
    public $work_from;
    public $work_to;
    public $director_fio;
    public $director_phone;
    public $director_email;
    public $manager_fio;
    public $manager_phone;
    public $manager_email;
    public $organisation_name;
    public $organisation_phone;
    public $type_service;
    public $type_car_change_tires;
    public $type_car_sell_tires;

    const SERVICE_CHANGE_TYRE = 1;
    const SERVICE_SELL_TIRE = 2;
    const SERVICE_SELL_DISC = 3;

    const CAR_TYPE_LIGHT = 1;
    const CAR_TYPE_HEAVY = 2;
    const CAR_TYPE_SPEC = 3;

    public static $listService = [
        self::SERVICE_CHANGE_TYRE => 'Шиномонтаж',
        self::SERVICE_SELL_TIRE   => 'Продажа шин',
        self::SERVICE_SELL_DISC   => 'Продажа дисков',
    ];

    public static $listCarType = [
        self::CAR_TYPE_LIGHT => 'Легковой',
        self::CAR_TYPE_HEAVY => 'Грузовой',
        self::CAR_TYPE_SPEC  => 'Спецтехника',
    ];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'index',
                    'city',
                    'street',
                    'building',
                    'phone',
                    'work_from',
                    'work_to',
                    'director_fio',
                    'director_phone',
                    'director_email',
                    'manager_fio',
                    'manager_phone',
                    'manager_email'
                ],
                'required'
            ],
            [
                [
                    'organisation_name',
                    'organisation_phone',
                    'type_service',
                    'type_car_change_tires',
                    'type_car_sell_tires',
                ],
                'safe'
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'name'                  => 'Название организации',
            'index'                 => 'Индекс',
            'city'                  => 'Город',
            'street'                => 'Улица',
            'building'              => 'Номер строения',
            'phone'                 => 'Телефон',
            'work_from'             => 'Часы работы от',
            'work_to'               => 'Часы работы до',
            'director_fio'          => 'Директор ФИО',
            'director_phone'        => 'Директор телефон',
            'director_email'        => 'Директор email',
            'manager_fio'           => 'Ответственный ФИО',
            'manager_phone'         => 'Ответственный телефон',
            'manager_email'         => 'Ответственный email',
            'type_service'          => 'Индекс',
            'type_car_change_tires' => 'Город',
            'type_car_sell_tires'   => 'Улица',
        ];
    }

    /**
     * @return string
     */
    public function getAddressMail()
    {
        return $this->index . ', ' . $this->city . ', ' . $this->street . ', ' . $this->building;
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
        $companyInfo->index = $this->index;
        $companyInfo->city = $this->city;
        $companyInfo->street = $this->street;
        $companyInfo->house = $this->building;
        $companyInfo->start_str = $this->work_from;
        $companyInfo->end_str = $this->work_to;

        return $companyInfo->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveDirector($idCompany)
    {
        $director = new CompanyMember();
        $director->company_id = $idCompany;
        $director->position = 'Директор';
        $director->phone = $this->director_phone;
        $director->email = $this->director_email;

        return $director->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveResponsible($idCompany)
    {
        $responsible = new CompanyMember();
        $responsible->company_id = $idCompany;
        $responsible->position = 'Ответственный за договорную работу';
        $responsible->phone = $this->manager_phone;
        $responsible->email = $this->manager_email;

        return $responsible->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveClients($idCompany)
    {
        foreach ($this->organisation_name as $key => $organisation_name) {
            if (!empty($organisation_name)) {
                $companyClient = new CompanyClient();
                $companyClient->company_id = $idCompany;
                $companyClient->name = $organisation_name;
                $companyClient->phone = $this->organisation_phone[$key];
                if (!$companyClient->save()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveTypeService($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_TIRE_SERVICE;
        $companyAttribute->value = $this->type_service;

        return $companyAttribute->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveTypeCarChangeTires($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_TYPE_CAR_CHANGE_TIRES;
        $companyAttribute->value = $this->type_car_change_tires;

        return $companyAttribute->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveTypeCarSellTires($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_TYPE_CAR_SELL_TIRES;
        $companyAttribute->value = $this->type_car_sell_tires;

        return $companyAttribute->save();
    }

}