<?php
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin boolean
 */
$this->title = \common\models\Company::$listType[$type]['ru'];

echo $this->render('_tabs',
    [
        'type' => $type,
    ]);
if (Yii::$app->user->can(User::ROLE_ADMIN)) {

    echo $this->render('_form',
        [
            'model'       => $model,
            'searchModel' => $searchModel,
            'type'        => $type,
        ]);

}
echo $this->render('_list',
    [
        'dataProvider' => $dataProvider,
        'searchModel'  => $searchModel,
        'type'         => $type,
        'admin'        => $admin
    ]);