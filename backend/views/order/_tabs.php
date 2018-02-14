<?php
use common\models\Service;
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = \Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
$serviceList = [Service::TYPE_WASH, Service::TYPE_SERVICE, Service::TYPE_TIRES, Service::TYPE_PARKING];
foreach ($serviceList as $type_id) {
    $items[] = [
        'label' => Service::$listType[$type_id]['ru'],
        'url' => ['/order/' . $action, 'type' => $type_id],
        'active' => $requestType == $type_id,
    ];
}

if ($action == 'archive') {
    $items[] = [
        'label' => 'Общее',
        'url' => ['/order/allarchive'],

    ];
}

echo Tabs::widget([
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
        'url' => ["/order/$action", 'type' => 3,
            'CompanySearch[card_number]' => Yii::$app->request->get('CompanySearch')['card_number'] ? Yii::$app->request->get('CompanySearch')['card_number'] : '',
            'CompanySearch[address]' => Yii::$app->request->get('CompanySearch')['address'] ? Yii::$app->request->get('CompanySearch')['address'] : '',
            'EntrySearch[day]' => isset(Yii::$app->request->get('EntrySearch')['day']) ? Yii::$app->request->get('EntrySearch')['day'] : ''],
        'active' => Yii::$app->controller->id == 'order' && $requestType == 3 && $requestSupType == 0,
    ];

    foreach (Company::$subTypeService as $type_id => $typeData) {
        $items[] = [
            'label' => Company::$subTypeService[$type_id]['ru'],
            'url' => ["/order/$action",
                'type' => 3,
                'sub' => $type_id,
                'CompanySearch[card_number]' => Yii::$app->request->get('CompanySearch')['card_number'] ? Yii::$app->request->get('CompanySearch')['card_number'] : '',
                'CompanySearch[address]' => Yii::$app->request->get('CompanySearch')['address'] ? Yii::$app->request->get('CompanySearch')['address'] : '',
                'EntrySearch[day]' => isset(Yii::$app->request->get('EntrySearch')['day']) ? Yii::$app->request->get('EntrySearch')['day'] : ''],
            'active' => Yii::$app->controller->id == 'order' && $requestType == 3 && $requestSupType == $type_id,
        ];
    }

    echo Tabs::widget([
        'encodeLabels' => false,
        'options' => ['style' => 'margin-top:5px;'],
        'items' => $items,
    ]);
}
// Подкатегории для сервиса