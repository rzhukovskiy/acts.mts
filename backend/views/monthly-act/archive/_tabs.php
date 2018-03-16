<?php
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $searchModel  common\models\search\MonthlyActSearch
 * @var $listType array[]
 */
//TODO переделать получение поля из модели

foreach ($listType as $type_id => $typeData) {
        $items[] = [
            'label'  => $typeData['ru'],
            'url'    => ['archive', 'type' => $type_id,
                'MonthlyActSearch[dateFrom]' => $searchModel->dateFrom,
                'MonthlyActSearch[dateTo]' => $searchModel->dateTo],
            'active' => $type == $type_id && !Yii::$app->request->get('company'),
        ];
        $items[] = [
            'label'  => 'Для компании',
            'url'    => [
                'archive',
                'type'                       => $type_id,
                'company'                    => true,
                'MonthlyActSearch[dateFrom]' => $searchModel->dateFrom,
                'MonthlyActSearch[dateTo]' => $searchModel->dateTo
            ],
            'active' => $type == $type_id && Yii::$app->request->get('company'),
        ];
}

$items[] = [
    'label'  => 'Должники',
    'url'    => ['archive', 'type' => 1, 'company' => true,
        'MonthlyActSearch[dateFrom]' => $searchModel->dateFrom,
        'MonthlyActSearch[dateTo]' => $searchModel->dateTo],
    'active' => $type == 1 && Yii::$app->request->get('company'),
];

$items[] = [
    'label'  => 'Мы должны',
    'url'    => ['archive', 'type' => -1, 'company' => false,
        'MonthlyActSearch[dateFrom]' => $searchModel->dateFrom,
        'MonthlyActSearch[dateTo]' => $searchModel->dateTo],
    'active' => $type == -1 && !(Yii::$app->request->get('company')),
];

$items[] = [
    'label'  => 'Общее',
    'url'    => ['archive', 'type' => -99,
        'MonthlyActSearch[dateFrom]' => $searchModel->dateFrom,
        'MonthlyActSearch[dateTo]' => $searchModel->dateTo],
    'active' => $type == -99,
];

echo Tabs::widget([
    'items' => $items,
]);