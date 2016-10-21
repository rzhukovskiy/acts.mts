<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Contact */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => 'Контакты', 'url' => ['/contact/list']];
$this->params['breadcrumbs'][] = 'Редактировать: ' . $model->name;

?>

<?= $this->render('_form',
    [
        'model' => $model,
    ]) ?>

