<?php

use yii\bootstrap\Tabs;

$this->title = 'Тендер №' . $model->id;

echo Tabs::widget([
    'items' => [
        ['label' => 'Тендеры', 'url' => ['tenders', 'id' => $model->company_id], 'active' => Yii::$app->controller->action->id == 'tenders'],
        ['label' => 'Тендер №' . $model->id, 'url' => ['fulltender', 'tender_id' => $model->id], 'active' => Yii::$app->controller->action->id == 'fulltender'],
        ['label' => 'Участники', 'url' => ['membersontender', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'membersontender'],
    ],
]);

?>

        <?= $this->render('_fulltender', [
            'model' => $model,
            'usersList' => $usersList,
            'newmodel' => $newmodel,
            'dataProvider' => $dataProvider,
        ]);
        ?>