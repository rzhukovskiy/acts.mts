<?php

use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $model \frontend\models\Act
 */

$request = Yii::$app->request;
$items = [
    [
        'label' => 'Мойка',
        'url' => ['/stat/view', 'type' => Company::TYPE_WASH],
        'active' => $request->get('type') == Company::TYPE_WASH,
    ],
    [
        'label' => 'Сервис',
        'url' => ['/stat/view', 'type' => Company::TYPE_SERVICE],
        'active' => $request->get('type') == Company::TYPE_SERVICE,
    ],
    [
        'label' => 'Шиномонтаж',
        'url' => ['/stat/view', 'type' => Company::TYPE_TIRES],
        'active' => $request->get('type') == Company::TYPE_TIRES,
    ],
    [
        'label' => 'Дезинфекция',
        'url' => ['/stat/view', 'type' => Company::TYPE_DISINFECT],
        'active' => $request->get('type') == Company::TYPE_DISINFECT,
    ],
    [
        'label' => 'Стоянка',
        'url' => ['/stat/view', 'type' => Company::TYPE_PARKING],
        'active' => $request->get('type') == Company::TYPE_PARKING,
    ],
    [
        'label' => 'Штрафы',
        'url' => ['/stat/view', 'type' => Company::TYPE_PENALTY],
        'active' => $request->get('type') == Company::TYPE_PENALTY,
    ],
    [
        'label' => 'Общая',
        'url' => '/stat/total',
        'active' => Yii::$app->controller->action->id == 'total',
    ],
];

echo Tabs::widget([
    'items' => $items,
]);