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

if(($action == 'new') || ($action == 'archive')) {
    foreach ($listType as $type_id => $typeData) {
        $items[] = [
            'label' => Company::$listType[$type_id]['ru'],
            'url' => ["/activity/$action", 'type' => $type_id],
            'active' => $requestType == $type_id,
        ];
    }
} else {
    $items[] = [
        'label' => Company::$listType[$type]['ru'],
        'url' => ["/activity/" . (($action == 'shownew') ? "new" : "archive"), 'type' => $type],
    ];
    $items[] = [
        'label' => 'Подробная статистика',
        'active' => $action == 'shownew' || $action == 'showarchive',
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);