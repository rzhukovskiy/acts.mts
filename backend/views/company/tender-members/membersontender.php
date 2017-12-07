<?php

use yii\bootstrap\Tabs;

$this->title = 'Участники тендера №' . $model->id;

echo Tabs::widget([
    'items' => [
        ['label' => 'Тендер №' . $model->id, 'url' => ['fulltender', 'tender_id' => $model->id], 'active' => Yii::$app->controller->action->id == 'fulltender'],
        ['label' => 'Участники', 'url' => ['membersontender', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'membersontender'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Участники тендера №' . $model->id ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_membersontender', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        ?>
    </div>
</div>