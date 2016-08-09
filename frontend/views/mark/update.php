<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Mark */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => 'Марки', 'url' => ['/mark/list']];
$this->params['breadcrumbs'][] = 'Редатировать: ' . $model->name;
?>
<div class="mark-update">

    <h1><?= $this->title ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
