<?php

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\TopicSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Message
 * @var $recipient common\models\User
 */

use yii\bootstrap\Tabs;

$this->title = 'Сообщения';

$action = Yii::$app->controller->action->id;
$requestDepartment = Yii::$app->request->get('department_id');

$items = [
    [
        'label' => 'Сообщения',
        'url' => ['/message/list', 'department_id' => $recipient->department->id],
        'active' => false,
    ],
    [
        'label' => 'Переписка',
        'url' => '#',
        'active' => true,
    ]
];
?>
<div class="user-index">
    <?= Tabs::widget([
        'items' => $items,
    ]); ?>

    <div class="panel panel-primary">
        <div class="panel-heading"><?= $model->topic->topic ?></div>
        <div class="panel-body">
            <?= $this->render('_list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]); ?>
            
            <?= $this->render('_form', [
                'model' => $model,
            ]); ?>
        </div>
    </div>
</div>