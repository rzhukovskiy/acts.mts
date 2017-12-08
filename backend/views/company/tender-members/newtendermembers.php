<?php

use yii\bootstrap\Tabs;

$this->title = 'Добавление участника';

echo Tabs::widget([
    'items' => [
        ['label' => 'Участники тендера №' . $id, 'url' => ['membersontender', 'id' => $id], 'active' => Yii::$app->controller->action->id == 'tendermembers'],
        ['label' => 'Добавление', 'url' => ['newtendermembers'], 'active' => Yii::$app->controller->action->id == 'newtendermembers'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление участника
    </div>
    <div class="panel-body">
        <?= $this->render('_newtendermembers', [
            'model' => $model,
            'id' => $id,
        ]);
        ?>
    </div>
</div>