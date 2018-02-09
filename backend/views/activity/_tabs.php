<?php
use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];

if(($action == 'new') || ($action == 'new2') || ($action == 'archive')) {
    foreach ($listType as $type_id => $typeData) {
        $items[] = [
            'label' => Company::$listType[$type_id]['ru'],
            'url' => ["/activity/$action", 'type' => $type_id],
            'active' => $requestType == $type_id,
        ];
    }
} else if($action == 'tender') {
    $items[] = [
        'label' => 'Мойка',
        'url' => ["/activity/$action", 'type' => 1],
        'active' => $requestType == 1,
    ];
    $items[] = [
        'label' => 'Шиномонтаж',
        'url' => ["/activity/$action", 'type' => 7],
        'active' => $requestType == 7,
    ];
} else if($action == 'showtender') {
    $items[] = [
        'label' => $requestType == 1 ? 'Мойка' : 'Шиномонтаж',
        'url' => ["/activity/tender", 'type' => $requestType],
    ];
    $items[] = [
        'label' => 'Подробная статистика',
        'active' => $action == 'showtender',
    ];
} else {
    $items[] = [
        'label' => Company::$listType[$type]['ru'],
        'url' => ["/activity/" . (($action == 'shownew') ? "new" : "archive"), 'type' => $type],
    ];
    $items[] = [
        'label' => 'Подробная статистика',
        'active' => $action == 'shownew' || $action == 'shownew' || $action == 'showarchive',
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);