<?php
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$nameUpdTab = '';

if(Yii::$app->controller->action->id == 'update') {
    $nameUpdTab = 'Изменение шаблона';
} else {
    $nameUpdTab = 'Добавить';
}

echo Tabs::widget([
    'items' => [
        ['label' => 'Шаблоны', 'url' => ['list'], 'active' => Yii::$app->controller->action->id == 'list'],
        ['label' => $nameUpdTab, 'url' => ['add'], 'active' => ((Yii::$app->controller->action->id == 'add') || (Yii::$app->controller->action->id == 'update'))],
    ],
]);