<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyInfo
 */

use common\models\Company;
use yii\bootstrap\Tabs;

$this->title = 'Редактирование ' . $model->company->name;

$items = [
    [
        'label' => Company::$listType[$model->company->type]['ru'],
        'url' => ['archive', 'type' => $model->company->type],
        'active' => \Yii::$app->controller->action->id == 'list',
    ],
    [
        'label' => 'Редактирование',
        'url' => '#',
        'active' => \Yii::$app->controller->action->id == 'update',
    ],
];

echo Tabs::widget( [
    'items' => $items,
] );

echo $this->render('/company-info/_form', [
    'model' => $model,
]);