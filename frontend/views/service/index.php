<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel common\models\search\ServiceSearch
 */

$this->title = 'Услуги';
?>

<?= $this->render('_tabs', [
    'model' => $model,
]) ?>

<?= $this->render('_form', [
    'model' => $model,
    'searchModel' => $searchModel,
]) ?>

<?= $this->render('_list', [
    'dataProvider' => $dataProvider,
]) ?>
