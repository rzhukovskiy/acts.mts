<?php
use yii\bootstrap\Html;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 */
$this->title = 'Редактирование пользователя ' . Html::encode($model->username);

$items = [
    [
        'label' => 'Пользователи',
        'url' => Yii::$app->getRequest()->referrer,
    ],
    [
        'label' => 'Редактирование',
        'url' => '#',
        'active' => true,
    ],
];

echo Tabs::widget( [
    'items' => $items,
] );
?>
<div class="user-update">
    <div class="panel panel-primary">
        <div class="panel-heading">Редактировать пользователя</div>
        <div class="panel-body">
            <?= $this->render('_form-update', [
                'model' => $model,
                'companyDropDownData' => $companyDropDownData,
            ]) ?>
        </div>
    </div>
</div>