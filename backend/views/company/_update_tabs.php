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
        'url' => [Company::$listStatus[$model->status]['en'], 'type' => $model->type],
    ],
    [
        'label' => 'Процесс',
        'url' => ['company/update', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'update',
    ],
    [
        'label' => 'Инфо',
        'url' => ['company/info', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'info',
    ],
    [
        'label' => 'Сотрудники',
        'url' => ['company/member', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'member',
    ],
];
if ($model->type == Company::TYPE_OWNER) {
    $items[] = [
        'label' => 'Водители',
        'url' => ['company/driver', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'driver',
    ];
}

echo Tabs::widget([
    'items' => $items,
]);