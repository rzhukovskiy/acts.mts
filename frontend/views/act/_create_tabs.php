<?php
use yii\bootstrap\Tabs;
use common\models\Company;
use common\models\Service;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $role string
 */

$request = Yii::$app->request;
$items = [];

/** @var Company $company */
$company = Yii::$app->user->identity->company;
if ($company->type == Company::TYPE_UNIVERSAL) {
    foreach ($company->serviceTypes as $serviceType) {
        $items[] = [
            'label' => Service::$listType[$serviceType->type]['ru'],
            'url' => ['create', 'type' => $serviceType->type],
            'active' => $request->get('type') == $serviceType->type,
        ];
    }
}
if ($company->is_main) {
    $items[] = [
        'label' => 'Массовая дезинфекция',
        'url' => ['disinfect'],
        'active' => Yii::$app->controller->action->id == 'disinfect',
    ];
}

echo Tabs::widget([
    'items' => $items,
]);