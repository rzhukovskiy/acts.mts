<?php

use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $action string
 */

$request = Yii::$app->request;
$items = [
    [
        'label' => 'Мойка',
        'url' => ['/stat/list', 'type' => Company::TYPE_WASH, 'group' => $action],
        'active' => $request->get('type') == Company::TYPE_WASH,
    ],
    [
        'label' => 'Сервис',
        'url' => ['/stat/list', 'type' => Company::TYPE_SERVICE, 'group' => $action],
        'active' => $request->get('type') == Company::TYPE_SERVICE,
    ],
    [
        'label' => 'Шиномонтаж',
        'url' => ['/stat/list', 'type' => Company::TYPE_TIRES, 'group' => $action],
        'active' => $request->get('type') == Company::TYPE_TIRES,
    ],
    [
        'label' => 'Дезинфекция',
        'url' => ['/stat/list', 'type' => Company::TYPE_DISINFECT, 'group' => $action],
        'active' => $request->get('type') == Company::TYPE_DISINFECT,
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