<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Mark */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => 'Марки', 'url' => ['/mark/list']];
$this->params['breadcrumbs'][] = 'Редатировать: ' . $model->name;
?>
<div class="mark-update">
    <div class="panel panel-primary">
        <div class="panel-heading">Редактировать тип</div>
        <div class="panel-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
