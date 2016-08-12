<?php
use yii\bootstrap\Html;

/**
 * @var $this \yii\web\View
 */
$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form-update', [
        'model' => $model,
        'companyDropDownData' => $companyDropDownData,
    ]) ?>
</div>