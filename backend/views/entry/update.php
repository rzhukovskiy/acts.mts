<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Entry
 */

use yii\bootstrap\Html;
use yii\bootstrap\Tabs;

$this->title = 'Редактирование записи на мойку ' . Html::encode($model->company->name);

$items = [
    [
        'label' => 'Мойка',
        'url' => ['wash/view', 'id' => $model->company_id, 'Entry[day]' => date('d-m-Y', $model->start_at)],
    ],
    [
        'label' => 'Запись',
        'url' => '#',
        'active' => true,
    ],
];

echo Tabs::widget( [
    'items' => $items,
] );
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Редактирование записи на мойку <?= $model->company->name ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]); ?>
    </div>
</div>