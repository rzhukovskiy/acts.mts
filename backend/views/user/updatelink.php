<?php

use yii\bootstrap\Tabs;

$this->title = 'Изменение привязки';

$items = [];
$items[] = [
    'label' => 'Привязка компаний',
    'url' => ['linking', 'type' => $type],
    'active' => Yii::$app->controller->action->id == 'linking',
];
$items[] = [
    'label' => 'Редактирование привязки',
    'url' => ['updatelink', 'id' => $model->id],
    'active' => Yii::$app->controller->action->id == 'updatelink',
];

echo Tabs::widget([
    'items' => $items,
]);

echo $this->render('_formLink', [
    'model' => $model,
    'type' => $type,
    'authorMembers' => $authorMembers,
    'arrCompany' => $arrCompany,
]);

?>