<?php
use common\models\Department;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = Yii::$app->controller->action->id;
$requestDepartment = Yii::$app->request->get('department_id');

$items = [];
foreach (Department::find()->active()->all() as $department) {
    $items[] = [
        'label' => $department->name,
        'url' => ['/message/list', 'department_id' => $department->id],
        'active' => $action == 'list' && $requestDepartment == $department->id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);