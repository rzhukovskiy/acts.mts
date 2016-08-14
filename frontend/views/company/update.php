<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Company
 */

use common\models\Company;
use common\models\Service;

$this->title = 'Редактирование ' . $model->name;

echo $this->render(Company::$listType[$model->type]['en'] . '/_form', [
    'model' => $model,
]);

foreach (Service::$listType as $id => $type) {
    if (($model->type == Company::TYPE_OWNER ||
            $model->type == Company::TYPE_UNIVERSAL ||
            $model->type == $id
        ) && Service::findAll(['type' => $type, 'is_fixed' => 1])
    ) {
        echo $this->render('_price_form', [
            'model' => $model,
            'type' => $id,
        ]);
    }
}