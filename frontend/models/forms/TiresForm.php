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

}