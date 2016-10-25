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


}