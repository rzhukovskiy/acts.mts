<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Услуги';
?>

<?= $this->render('_form', [
    'model' => $model,
]) ?>

<?= $this->render('_list', [
    'dataProvider' => $dataProvider,
]) ?>
