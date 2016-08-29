<?php

use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$request = Yii::$app->request;
$items = [
    [
        'label' => 'Мойка',
        'url' => ['/statistic/list', 'type' => Company::TYPE_WASH],
        'active' => $request->get('type') == Company::TYPE_WASH,
    ],
    [
        'label' => 'Сервис',
        'url' => ['/statistic/list', 'type' => Company::TYPE_SERVICE],
        'active' => $request->get('type') == Company::TYPE_SERVICE,
    ],
    [
        'label' => 'Шиномонтаж',
        'url' => ['/statistic/list', 'type' => Company::TYPE_TIRES],
        'active' => $request->get('type') == Company::TYPE_TIRES,
    ],
    [
        'label' => 'Дезинфекция',
        'url' => ['/statistic/list', 'type' => Company::TYPE_DISINFECT],
        'active' => $request->get('type') == Company::TYPE_DISINFECT,
    ],
    [
        'label' => 'Общая',
        'url' => '/statistic/total',
        'active' => Yii::$app->controller->action->id == 'total',
    ],
];

echo Tabs::widget([
    'items' => $items,
]);