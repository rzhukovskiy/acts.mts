<?php

use yii\bootstrap\Tabs;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Участники';

echo Tabs::widget([
    'items' => [
        ['label' => 'Мойка', 'url' => ['tendermembers'], 'active' => Yii::$app->controller->action->id == 'tendermembers'],
        ['label' => 'Шиномонтаж', 'url' => ['tendermembers'], 'active' => Yii::$app->controller->action->id == 'tendermembers'],
        ['label' => 'Мойка и шиномонтаж', 'url' => ['tendermembers'], 'active' => Yii::$app->controller->action->id == 'tendermembers'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Участники' ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_tendermembers', [
            'searchModel' => $searchModel,
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
        ?>
    </div>
</div>