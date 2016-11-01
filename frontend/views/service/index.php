<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel common\models\search\ServiceSearch
 * @var $admin boolean
 */

$this->title = 'Услуги';
?>

<?= $this->render('_tabs', [
    'model' => $model,
]) ?>

<?
if($admin){
    $this->render('_form', [
        'model' => $model,
        'searchModel' => $searchModel,
    ]);
}
?>

<?= $this->render('_list', [
    'dataProvider' => $dataProvider,
    'admin' => $admin,
]) ?>
