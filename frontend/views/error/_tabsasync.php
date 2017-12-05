<?php
use common\models\Act;
use common\models\search\ActSearch;
use common\models\Service;
use common\models\User;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $role string
 */

$request = Yii::$app->request;
$items = [];
if ($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) {
    foreach (Service::$listType as $type_id => $typeData) {
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_ASYNC]);
        $searchModel->service_type = $type_id;
        $badgeCount = $searchModel->search(Yii::$app->request->queryParams)->getCount();

        $items[] = [
            'label'  =>
                $typeData['ru'] .
                ($badgeCount ? ' <span class="label label-danger">' . $badgeCount . '</span>' : ''),
            'url'    => [Yii::$app->controller->action->id, 'type' => $type_id],
            'active' => $request->get('type') == $type_id && !$request->get('company'),
        ];
    }
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items'        => $items,
]);