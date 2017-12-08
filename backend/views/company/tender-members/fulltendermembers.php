<?php

use yii\bootstrap\Tabs;


$this->title = 'Редактирование участника';

echo Tabs::widget([
    'items' => [
        ['label' => 'Участники', 'url' => ['tendermembers'], 'active' => Yii::$app->controller->action->id == 'tendermembers'],
        ['label' => 'Редактирование участника', 'url' => ['fulltendermembers', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'fulltendermembers'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование участника №' . $model->id ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_fulltendermembers', [
            'model' => $model,
        ]);
        ?>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        Список тендеров
    </div>
    <div class="panel-body">
        <?= $this->render('_tenderlinks', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
        ?>
    </div>
</div>