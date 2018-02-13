<?php
use common\models\Department;
use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = Yii::$app->controller->action->id;
$requestDepartment = Yii::$app->request->get('department');

$items = [];
foreach (Department::find()->active()->all() as $department) {
    $items[] = [
        'label' => $department->name,
        'url' => ['/user/list', 'department' => $department->id],
        'active' => $action == 'list' && $requestDepartment == $department->id,
    ];
}

$items[] = [
    'label' => 'Привязка',
    'url' => ['/user/linking', 'type' => Company::TYPE_OWNER],
    'active' => Yii::$app->controller->action->id == 'linking',
];

echo Tabs::widget([
    'items' => $items,
]);