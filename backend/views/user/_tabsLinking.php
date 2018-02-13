<?php
use common\models\Department;
use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = Yii::$app->controller->action->id;
$request = Yii::$app->request;

// Основные вкладки
$items = [];
$items[] = [
    'label' => 'Сотрудники',
    'url' => ['/user/list', 'department' => 1],
    'active' => $action == 'list' && $request->get('department') == 1,
];
$items[] = [
    'label' => 'Привязка',
    'url' => ['/user/linking', 'type' => Company::TYPE_OWNER],
    'active' => Yii::$app->controller->action->id == 'linking',
];

echo Tabs::widget([
    'items' => $items,
]);
// Основные вкладки

// Подтипы
$items = [];
foreach (Company::$listType as $type_id => $typeData) {
    $items[] = [
        'label'  =>
            $typeData['ru'],
        'url'    => [Yii::$app->controller->action->id, 'type' => $type_id],
        'active' => $request->get('type') == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);
// Подтипы