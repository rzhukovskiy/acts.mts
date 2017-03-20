<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 */

use yii\bootstrap\Tabs;

$this->title = 'Статистика данных';

$items = [
    [
        'label' => 'Статистка по количеству помятых машин',
        'url' => [
            'list',
            'type' => $searchModel->service_type,
            'group' => 'count',
            'ActSearch[dateFrom]' => $searchModel->dateFrom,
            'ActSearch[dateTo]' => $searchModel->dateTo,
        ],
        'active' => false,
    ],
    [
        'label' => 'Список',
        'url' => [
            'view',
            'type' => $searchModel->service_type,
            'group' => 'count',
            'count' => $dataProvider->count,
            'ActSearch[dateFrom]' => $searchModel->dateFrom,
            'ActSearch[dateTo]' => $searchModel->dateTo,
        ],
        'active' => false,
    ],
    [
        'label' => 'Детализация',
        'url' => ['#'],
        'active' => true,
    ]
];

echo Tabs::widget([
    'items' => $items,
]);

echo $this->render('_detail', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);

