<?php
use yii\bootstrap\Html;

/**
 * @var $this \yii\web\View
 */
$this->title = 'Редактирование пользователя ' . Html::encode($model->username);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="panel panel-primary">
        <div class="panel-heading">Редактировать пользователя</div>
        <div class="panel-body">
            <?= $this->render('_form-update', [
                'model' => $model,
                'companyDropDownData' => $companyDropDownData,
            ]) ?>
        </div>
    </div>
</div>