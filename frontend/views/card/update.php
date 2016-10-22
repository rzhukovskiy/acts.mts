<?php
/**
 * @var $this yii\web\View
 * @var $model common\models\Card
 * @var $companyDropDownData array
 *
 */

$this->title = 'Редактирование карты: ' . $model->number . ' - ' . $model->company->name;


echo $this->render('_form', [
    'model' => $model,
    'companyDropDownData' => $companyDropDownData,
]);