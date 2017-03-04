<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 */

use yii\bootstrap\Tabs;

$this->title = 'Анализ данных';

if ($group == 'city') {
    $items[] = [
        'label' => 'Анализ по городам',
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
        'label' => 'Анализ общий',
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
]);

