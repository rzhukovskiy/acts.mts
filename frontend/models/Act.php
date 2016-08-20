<?php

namespace frontend\models;

use common\models\Act as CommonAct;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Act model
 * @package common\models
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $partner_id
 * @property integer $type_id
 * @property integer $mark_id
 * @property integer $card_id
 * @property integer $status
 * @property integer $expense
 * @property integer $income
 * @property integer $profit
 * @property integer $service_type
 * @property integer $served_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $check
 * @property string $number
 * @property string $extra_number
 *
 * @property array $serviceList
 * @property array $time_str
 *
 * @property Company $client
 * @property Company $partner
 * @property Type $type
 * @property Mark $mark
 * @property Card $card
 * @property Car $car
 * @property ActScope[] $scopes
 */
class Act extends CommonAct
{
    public $countServe; // сколько обслужено машин (кол-во актов)

    /**
     * Атрибуты для статистики
     */
    public $month;
    public $year;
    public $numActs;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['countServe'], 'safe'],
        ];

        return array_merge(parent::rules(), $rules);
    }

    public function attributeLabels()
    {
        $labels = [
            'countServe' => 'Обслужено',
        ];

        return array_merge($labels, parent::attributeLabels());
    }


}