<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Company
 * @var $admin bool
 */
use common\models\Car;
use common\models\Company;
use common\models\Service;

foreach (Service::$listType as $type_id => $type) {
    if (($model->type == Company::TYPE_OWNER ||
            $model->type == Company::TYPE_UNIVERSAL ||
            $model->type == $type_id
        ) && Service::findAll(['type' => $type, 'is_fixed' => 1])
    ) {
        echo $this->render('/company-service/_form', [
            'model' => $model,
            'type' => $type_id,
        ]);
    }
}

if ($model->type == Company::TYPE_OWNER) {
    echo $this->render('/car/_form', [
        'model' => new Car(),
        'companyModel' => $model,
    ]);
}
?>