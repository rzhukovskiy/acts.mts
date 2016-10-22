<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Car
 */

$this->title = 'Редактирование ' . $model->number;

echo $this->render('_form', [
    'model' => $model,
    'companyModel' => $model->company,
]);