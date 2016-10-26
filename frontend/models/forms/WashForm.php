<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace frontend\models\forms;

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
     * @return string
     */
    public function getAddressMail()
    {
        return $this->index . ', ' . $this->city . ', ' . $this->street . ', ' . $this->building;
    }

}