<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Company
 * @var $modelEntry \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
 */

use yii\bootstrap\Html;
use yii\bootstrap\Tabs;

$this->title = 'Запись на мойку ' . Html::encode($model->name);

$items = [
    [
        'label' => 'Мойки',
        'url' => ['wash/list', 'companySearch[address]' => $model->address],
    ],
    [
        'label' => 'Запись',
        'url' => '#',
        'active' => true,
    ],
];

echo Tabs::widget( [
    'items' => $items,
] );

echo $this->render('_day_form', [
    'model' => $modelEntry,
]);

echo $this->render('_full_view', [
    'model' => $model,
    'modelEntry' => $modelEntry,
    'searchModel' => $searchModel,
]);

echo $this->render('_entry_list', [
    'searchModel' => $searchModel,
]);