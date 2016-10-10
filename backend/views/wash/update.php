<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Entry
 */

use yii\bootstrap\Html;

$this->title = 'Запись на мойку ' . Html::encode($model->name);

$items = [
    [
        'label' => 'Мойка',
        'url' => ['wash/view', 'id' => $model->company_id],
    ],
    [
        'label' => 'Запись',
        'url' => '#',
        'active' => true,
    ],
];
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Редактирпование записи на мойку <?= $model->name ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]); ?>
    </div>
</div>