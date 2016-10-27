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

class ServiceForm extends Model
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
    public $official_dealer;
    public $nonofficial_dealer;
    public $organisation_name;
    public $organisation_phone;
    public $service_type;
    public $service_hour_norm;

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
                    'official_dealer',
                    'nonofficial_dealer',
                    'organisation_name',
                    'organisation_phone',
                    'service_type',
                    'service_hour_norm',
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
            'name'               => 'Название организации',
            'index'              => 'Индекс',
            'city'               => 'Город',
            'street'             => 'Улица',
            'building'           => 'Номер строения',
            'phone'              => 'Телефон',
            'work_from'          => 'Часы работы от',
            'work_to'            => 'Часы работы до',
            'director_fio'       => 'Директор ФИО',
            'director_phone'     => 'Директор телефон',
            'director_email'     => 'Директор email',
            'manager_fio'        => 'Ответственный ФИО',
            'manager_phone'      => 'Ответственный телефон',
            'manager_email'      => 'Ответственный email',
            'official_dealer'    => 'Официальный дилер',
            'nonofficial_dealer' => 'Неофициальный дилер',
            'organisation_name'  => 'Название организации',
            'organisation_phone' => 'Телефон организации',
            'service_type'       => 'Вид работ',
            'service_hour_norm'  => 'Норма часа',
        ];
    }

    public function getDealerMark()
    {
        $dealerMark = [
            'official_dealer_mark'    => explode(',', $this->official_dealer),
            'nonofficial_dealer_mark' => explode(',', $this->nonofficial_dealer),
        ];

        return $dealerMark;
    }

    public function getNormHour()
    {
        $normHour = [];
        foreach ($this->service_type as $key => $service_type) {
            if (!empty($service_type)) {
                $normHour[] = [
                    'service_type'      => $service_type,
                    'service_hour_norm' => $this->service_hour_norm[$key],
                ];
            }
        }

        return $normHour;
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
    public function saveDealerMark($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_SERVICE_MARK;
        $companyAttribute->value = $this->getDealerMark();

        return $companyAttribute->save();
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveNormHour($idCompany)
    {
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_SERVICE_TYPE;
        $companyAttribute->value = $this->getNormHour();

        return $companyAttribute->save();
    }
}