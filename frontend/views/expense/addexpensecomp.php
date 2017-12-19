<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $admin null|bool
 */

use common\models\ExpenseCompany;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $listType array[]
 */

$this->title = ExpenseCompany::$listType[$model->type]['ru'];

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
foreach ($listType as $type_id => $typeData) {
        $items[] = [
            'label' => ExpenseCompany::$listType[$type_id]['ru'],
            'url' => ["/expense/$action", 'type' => $type_id],
            'active' => Yii::$app->controller->id == 'expense' && $requestType == $type_id,
        ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);

echo $this->render('_addexpensecomp', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'model' => $model,
]);