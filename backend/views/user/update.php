<?php
use yii\bootstrap\Html;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $userModel common\models\User
 */
$this->title = 'Редактирование пользователя ' . Html::encode($userModel->username);

$items = [
    [
        'label' => 'Пользователи',
        'url' => ['user/list', 'department' => $userModel->department->id],
    ],
    [
        'label' => 'Редактирование',
        'url' => '#',
        'active' => true,
    ],
];

echo Tabs::widget( [
    'items' => $items,
] );
?>
<div class="user-update">
    <div class="panel panel-primary">
        <div class="panel-heading">Редактировать пользователя</div>
        <div class="panel-body">
            <?= $this->render('_form-update', [
                'model' => $model,
                'userModel'=>$userModel,
                //'companyDropDownData' => $companyDropDownData,
            ]) ?>
        </div>
    </div>
</div>