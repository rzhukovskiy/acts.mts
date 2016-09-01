<?php
use yii\bootstrap\Tabs;
use common\models\Company;
use common\models\Service;
use common\models\User;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $role string
 */

$request = Yii::$app->request;
$items = [];

switch ($role) {
    case User::ROLE_WATCHER:
    case User::ROLE_ADMIN:
        foreach (Service::$listType as $type_id => $typeData) {
            $items[] = [
                'label' => $typeData['ru'],
                'url' => ['list', 'type' => $type_id],
                'active' => $request->get('type') == $type_id && !$request->get('company'),
            ];
            $items[] = [
                'label' => 'Для компании',
                'url' => ['list', 'type' => $type_id, 'company' => true],
                'active' => $request->get('type') == $type_id && $request->get('company'),
            ];
        }
        break;

    case User::ROLE_CLIENT:
        foreach (Service::$listType as $type_id => $typeData) {
            $items[] = [
                'label' => $typeData['ru'],
                'url' => ['list', 'type' => $type_id, 'company' => true],
                'active' => $request->get('type') == $type_id && $request->get('company'),
            ];
        }
        break;

    case User::ROLE_PARTNER:
        /** @var Company $company */
        $company = Yii::$app->user->identity->company;
        if ($company->type == Company::TYPE_UNIVERSAL) {
            foreach ($company->serviceTypes as $serviceType) {
                $items[] = [
                    'label' => Service::$listType[$serviceType->type]['ru'],
                    'url' => ['list', 'type' => $serviceType->type],
                    'active' => $request->get('type') == $serviceType->type,
                ];
            }
        }
        break;
}

echo Tabs::widget([
    'items' => $items,
]);