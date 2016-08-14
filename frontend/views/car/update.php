<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Car
 */

use common\models\Car;
use common\models\Company;
use common\models\Service;

$this->title = 'Редактирование ' . $model->number;

echo $this->render('_form', [
    'model' => $model,
    'companyModel' => $model->company,
]);