<?php
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $listType array[]
 */

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
foreach ($listType as $type_id => $typeData) {
    $items[] = [
        'label' => Company::$listType[$type_id]['ru'] . ($typeData['badge'] ? ' <span class="label label-success">' . $typeData['badge'] . '</span>' : ''),
        'url' => ["/company/$action", 'type' => $type_id],
        'active' => Yii::$app->controller->id == 'company' && $requestType == $type_id,
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);

// Подкатегории для сервиса
if($requestType == 3) {

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
        'options' => ['style' => 'margin-top:5px;'],
        'items' => $items,
    ]);
}
// Подкатегории для сервиса