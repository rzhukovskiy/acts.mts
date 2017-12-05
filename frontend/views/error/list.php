<?php
/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $company null|integer
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $admin boolean
 */

$request = Yii::$app->request;

if((Yii::$app->controller->action->id == 'list') && (Yii::$app->controller->id == 'error')) {
    $this->title = 'Ошибочные акты';

    echo $this->render('_tabs',
        [
            'role' => $role,
        ]);

} elseif((Yii::$app->controller->action->id == 'losses') && (Yii::$app->controller->id == 'error')) {
    $this->title = 'Убыточные акты';

    echo $this->render('_tabslose',
        [
            'role' => $role,
        ]);

} elseif((Yii::$app->controller->action->id == 'async') && (Yii::$app->controller->id == 'error')) {
    $this->title = 'Асинхронные акты ';

    echo $this->render('_tabsasync',
        [
            'role' => $role,
        ]);

} else {
    $this->title = 'Акты';

    echo $this->render('_tabs',
        [
            'role' => $role,
        ]);

}

echo $this->render('_list',
[
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'admin'        => $admin,
]);


