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
        'url'    => ['list', 'type' => $type_id, 'MonthlyActSearch[act_date]' => $searchModel->act_date],
        'active' => $type == $type_id && !Yii::$app->request->get('company'),
    ];
    $items[] = [
        'label'  => 'Для компании',
        'url'    => [
            'list',
            'type'                       => $type_id,
            'company'                    => true,
            'MonthlyActSearch[act_date]' => $searchModel->act_date
        ],
        'active' => $type == $type_id && Yii::$app->request->get('company'),
    ];
}

echo Tabs::widget([
    'items' => $items,
]);