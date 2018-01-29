<?php

use common\models\Company;
use yii\bootstrap\Tabs;

$request = Yii::$app->request;
$items = [];

foreach (Company::$listType as $type_id => $typeData) {
    $items[] = [
        'label'  =>
            $typeData['ru'],
        'url'    => [Yii::$app->controller->action->id, 'type' => $type_id],
        'active' => $request->get('type') == $type_id,
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items'        => $items,
]);