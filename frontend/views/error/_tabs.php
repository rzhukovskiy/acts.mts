<?php
use yii\bootstrap\Tabs;
use common\models\search\ActSearch;
use common\models\Act;
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
            $searchModel = new ActSearch(['scenario' => Act::SCENARIO_ERROR]);
            $searchModel->service_type = $type_id;
            $badgeCount = $searchModel->search(Yii::$app->request->queryParams)->getCount();

            $items[] = [
                'label' => $typeData['ru'] . ($badgeCount ? ' <span class="label label-danger">' . $badgeCount . '</span>' : ''),
                'url' => [Yii::$app->controller->action->id, 'type' => $type_id],
                'active' => $request->get('type') == $type_id && !$request->get('company'),
            ];
        }
        break;
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);