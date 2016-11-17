<?php
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $searchModel  common\models\search\MonthlyActSearch
 * @var $listType array[]
 */
//TODO переделать получение поля из модели


$items = [];
foreach ($listType as $type_id => $typeData) {
    $items[] = [
        'label'  => $typeData['ru'],
        'url'    => [
            'archive',
            'type' => $type_id
            // , 'MonthlyActSearch[act_date]' => $searchModel->act_date
        ],
        'active' => Yii::$app->controller->id == 'monthly-act' && $type == $type_id,
    ];
}


echo Tabs::widget([
    'items' => $items,
]);