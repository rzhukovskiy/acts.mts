<?php

use yii\bootstrap\Tabs;

$this->title = 'Связь конкурентов с тендерами';

echo Tabs::widget([
    'items' => [
        ['label' => 'Участники', 'url' => ['tendermembers'], 'active' => Yii::$app->controller->action->id == 'tendermembers'],
        ['label' => 'Связь', 'url' => ['newtenderlinks'], 'active' => Yii::$app->controller->action->id == 'newtenderlinks'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Связь конкурентов с тендерами
    </div>
    <div class="panel-body">
        <?= $this->render('_newtenderlinks', [
            'model' => $model,
        ]);
        ?>
    </div>
</div>

