<?php

use yii\bootstrap\Tabs;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Участники';

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Участники' ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_tendermembers', [
            'searchModel' => $searchModel,
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
        ?>
    </div>
</div>