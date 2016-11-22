<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Company
 */

$this->title = 'Прайс ' . $model->name;

echo $this->render('_update_tabs', [
    'model' => $model,
]);

echo $this->render('form/_price', [
    'model' => $model,
]);