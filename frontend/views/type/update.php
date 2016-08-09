<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Type */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => 'Виды', 'url' => ['/type/list']];
$this->params['breadcrumbs'][] = 'Редатировать: ' . $model->name;
?>
<div class="type-update">

    <h1><?= $this->title ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
