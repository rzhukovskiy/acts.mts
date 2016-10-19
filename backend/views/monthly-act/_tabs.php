<?php
use common\models\Service;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $model common\models\Company
 * @var $active string
 */

foreach (Service::$listType as $type_id => $typeData) {
    $items[] = [
        'label'  => $typeData['ru'],
        'url'    => ['list', 'type' => $type_id],
        'active' => $type == $type_id && !Yii::$app->request->get('company'),
    ];
    $items[] = [
        'label'  => 'Для компании',
        'url'    => ['list', 'type' => $type_id, 'company' => true],
        'active' => $type == $type_id && Yii::$app->request->get('company'),
    ];
}

echo Tabs::widget([
    'items' => $items,
]);