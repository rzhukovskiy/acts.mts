<?php

use yii\bootstrap\Tabs;

$this->title = 'Новый тендер клиента';

echo Tabs::widget([
    'items' => [
        ['label' => 'Тендеры', 'url' => ['tenders', 'id' => $id], 'active' => Yii::$app->controller->action->id == 'tenders'],
        ['label' => 'Новый тендер', 'url' => ['newtender', 'id' => $id], 'active' => Yii::$app->controller->action->id == 'newtender'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление тендера
    </div>
    <div class="panel-body">
        <?= $this->render('_newtender', [
            'id' => $id,
            'model' => $model,
            'usersList' => $usersList,
        ]);
        ?>
    </div>
</div>