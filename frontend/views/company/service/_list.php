<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $admin null|bool
 */

use yii\grid\GridView;
use common\models\Company;
use yii\bootstrap\Tabs;

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');
$requestSupType = 0;

if(Yii::$app->request->get('sub')) {
    $requestSupType = Yii::$app->request->get('sub');
}

$items = [];

$items[] = [
    'label' => 'Все',
    'url' => ["/company/$action", 'type' => 3],
    'active' => Yii::$app->controller->id == 'company' && $requestType == 3 && $requestSupType == 0,
];

foreach (Company::$subTypeService as $type_id => $typeData) {
    $items[] = [
        'label' => Company::$subTypeService[$type_id]['ru'],
        'url' => ["/company/$action", 'type' => 3, 'sub' => $type_id],
        'active' => Yii::$app->controller->id == 'company' && $requestType == 3 && $requestSupType == $type_id,
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список сервисов
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                'name',
                'address',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'contentOptions' => ['style' => 'min-width: 80px'],
                    'visible' => $admin,
                ],
            ],
        ]);
        ?>
    </div>
</div>