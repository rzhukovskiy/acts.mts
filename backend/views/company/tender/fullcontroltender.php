<?php

use yii\bootstrap\Tabs;

$this->title = 'Контроль денежных средств №' . $model->id

?>

<?php echo Tabs::widget([
    'items' => [
        ['label' => 'Полный список', 'url' => ['controltender'], 'active' => Yii::$app->controller->action->id == 'controltender'],
        ['label' => 'Редактирование №' . $model->id, 'url' => ['fullcontroltender', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'fullcontroltender'],
    ],
]);
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Контроль денежных средств №' . $model->id ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_fullcontroltender', [
            'model' => $model,
            'usersList' => $usersList,
        ]);
        ?>
    </div>
</div>
