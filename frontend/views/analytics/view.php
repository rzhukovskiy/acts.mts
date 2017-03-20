<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 */

use yii\bootstrap\Tabs;

$this->title = 'Статистика данных';

if ($group == 'count') {
    $items[] = [
        'label' => 'Статистка по количеству помятых машин',
        'url' => [
            'list',
            'group' => $group,
            'ActSearch[dateFrom]' => $searchModel->dateFrom,
            'ActSearch[dateTo]' => $searchModel->dateTo,
        ],
        'active' => false,
    ];
}
if ($group == 'city') {
    $items[] = [
        'label' => 'Статистика помытых машин по городам',
        'url' => [
            'list',
            'type' => $searchModel->service_type,
            'group' => $group,
            'ActSearch[dateFrom]' => $searchModel->dateFrom,
            'ActSearch[dateTo]' => $searchModel->dateTo,
        ],
        'active' => false,
    ];
}
if ($group == 'type') {
    $items[] = [
        'label' => 'Общая статистика',
        'url' => [
            'list',
            'group' => $group,
            'ActSearch[dateFrom]' => $searchModel->dateFrom,
            'ActSearch[dateTo]' => $searchModel->dateTo,
        ],
        'active' => false,
    ];
}
$items[] = [
    'label' => 'Список',
    'url' => ['#'],
    'active' => true,
];

echo Tabs::widget([
    'items' => $items,
]);

echo $this->render('_view', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'group' => $group,
    'count' => $count,
]);

