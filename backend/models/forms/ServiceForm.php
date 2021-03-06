<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace backend\models\forms;

use common\models\CompanyAttributes;
use common\models\CompanyClient;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use common\models\CompanyTime;
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
                    'phone',
                    'city'
                ],
                'required'
            ],
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
                'string'
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
            'name'               => '???????????????? ??????????????????????',
            'index'              => '????????????',
            'city'               => '??????????',
            'street'             => '??????????',
            'building'           => '?????????? ????????????????',
            'phone'              => '??????????????',
            'work_from'          => '???????? ???????????? ????',
            'work_to'            => '???????? ???????????? ????',
            'director_fio'       => '???????????????? ??????',
            'director_phone'     => '???????????????? ??????????????',
            'director_email'     => '???????????????? email',
            'manager_fio'        => '?????????????????????????? ??????',
            'manager_phone'      => '?????????????????????????? ??????????????',
            'manager_email'      => '?????????????????????????? email',
            'official_dealer'    => '?????????????????????? ??????????',
            'nonofficial_dealer' => '?????????????????????????? ??????????',
            'organisation_name'  => '???????????????? ??????????????????????',
            'organisation_phone' => '?????????????? ??????????????????????',
            'service_type'       => '?????? ??????????',
            'service_hour_norm'  => '?????????? ????????',
        ];
    }

    /**
     * @return array
     */
    public function getDealerMark()
    {
        $dealerMark = [
            'official_dealer_mark'    => explode(',', $this->official_dealer),
            'nonofficial_dealer_mark' => explode(',', $this->nonofficial_dealer),
        ];

        return $dealerMark;
    }

    /**
     * @return array
     */
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
        if (!$companyInfo->save()) {
            return false;
        }
        for ($day = 1; $day < 6; $day++) {
            $companyTime = new CompanyTime();
            $companyTime->company_id = $idCompany;
            $companyTime->day = $day;
            if ($this->work_from) {
                list($hrs, $mnts) = explode(':', trim($this->work_from));
                $companyTime->start_at = $hrs * 3600 + $mnts * 60;
            }
            if ($this->work_to) {
                list($hrs, $mnts) = explode(':', trim($this->work_to));
                $companyTime->end_at = $hrs * 3600 + $mnts * 60;
            }
            $companyTime->save();
        }

        return true;
    }

    /**
     * @param $idCompany
     * @return bool
     */
    public function saveDirector($idCompany)
    {
        if (empty($this->director_phone) && empty($this->director_email)) {
            return false;
        }
        $director = new CompanyMember();
        $director->company_id = $idCompany;
        $director->position = '????????????????';
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
        if (empty($this->manager_phone) && empty($this->manager_email)) {
            return false;
        }
        $responsible = new CompanyMember();
        $responsible->company_id = $idCompany;
        $responsible->position = '?????????????????????????? ???? ???????????????????? ????????????';
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
        if (empty($this->organisation_name) && empty($this->organisation_phone)) {
            return false;
        }
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
    public function saveDealerMarkAttribute($idCompany)
    {
        if (empty($this->official_dealer) && empty($this->nonofficial_dealer)) {
            return false;
        }
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
    public function saveNormHourAttribute($idCompany)
    {
        if (empty($this->service_type) && empty($this->service_hour_norm)) {
            return false;
        }
        $companyAttribute = new CompanyAttributes();
        $companyAttribute->company_id = $idCompany;
        $companyAttribute->type = CompanyAttributes::TYPE_SERVICE_TYPE;
        $companyAttribute->value = $this->getNormHour();

        return $companyAttribute->save();
    }
}