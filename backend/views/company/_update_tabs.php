<?php
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $model Company
 */

$items = [
    [
        'label' => Company::$listType[$model->type]['ru'],
        'url'   => [Company::$listStatus[$model->status]['en'], 'type' => $model->type],
    ],
    [
        'label'  => 'Процесс',
        'url'    => ['company/update', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'update',
    ],
    [
        'label'  => 'Инфо',
        'url'    => ['company/info', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'info',
    ],
    [
        'label'  => 'Сотрудники',
        'url'    => ['company/member', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'member',
    ],
    [
        'label'  => 'Данные заявки',
        'url'    => ['company/attribute', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'attribute',
    ]
];
if ($model->type == Company::TYPE_OWNER) {
    $items[] = [
        'label' => 'Водители',
        'url' => ['company/driver', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'driver',
    ];
}
$items[] = [
    'label' => 'Цены и ТС',
    'url' => ['company/price', 'id' => $model->id],
    'active' => \Yii::$app->controller->action->id == 'price',
];

echo Tabs::widget([
    'items' => $items,
]);