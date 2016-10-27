<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace backend\models\forms;

use common\models\CompanyClient;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use Yii;
use yii\base\Model;

class WashForm extends Model
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
                    'organisation_name',
                    'organisation_phone',
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
            'name'           => 'Название организации',
            'index'          => 'Индекс',
            'city'           => 'Город',
            'street'         => 'Улица',
            'building'       => 'Номер строения',
            'phone'          => 'Телефон',
            'work_from'      => 'Часы работы от',
            'work_to'        => 'Часы работы до',
            'director_fio'   => 'Директор ФИО',
            'director_phone' => 'Директор телефон',
            'director_email' => 'Директор email',
            'manager_fio'    => 'Ответственный ФИО',
            'manager_phone'  => 'Ответственный телефон',
            'manager_email'  => 'Ответственный email'
        ];
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
        if (empty($this->director_phone) && empty($this->director_email)) {
            return false;
        }
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
        if (empty($this->manager_phone) && empty($this->manager_email)) {
            return false;
        }
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

}