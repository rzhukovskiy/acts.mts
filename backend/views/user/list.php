<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $newUser \common\models\User
 */

$this->title = 'Пользователи';

?>
<div class="user-index">
    <?= $this->render('_tabs') ?>
    <div class="panel panel-primary">
        <div class="panel-heading">Добавить пользователя</div>
        <div class="panel-body">
            <?= $this->render('_form', [
                'model' => $newUser,
            ]) ?>
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">Пользователи<?php

            // Кнопки закрыть и открыть доступ для админов
            $currentUser = Yii::$app->user->identity;

            if(($currentUser->role == User::ROLE_ADMIN) || ($currentUser->id == 176)) {

            ?>
                <div class="header-btn pull-right">
                <a class="btn btn-success btn-sm" href="/user/closelogin?status=10" style="margin-right:15px;">Открыть доступ для всех</a>
                <a class="btn btn-danger btn-sm" href="/user/closelogin?status=0">Закрыть доступ для всех</a>
            </div>
            <?php
            }
            // Кнопки закрыть и открыть доступ для админов
            ?>
        </div>
        <div class="panel-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'summary' => false,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],

                    [
                        'attribute' => 'username',
                        'content' => function ($data) {
                            return Html::a($data->username, [
                                'update',
                                'id' => $data->id,
                            ]);
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{login}',
                        'buttons' => [
                            'login' => function ($url, $model, $key) {
                                if ($model->role != \common\models\User::ROLE_ADMIN)
                                    return Html::a('Войти', ['/user/login', 'id' => $model->id], ['class' => 'btn btn-xs btn-default']);
                            },
                        ]
                    ],
                    [
                        'attribute' => 'status',
                        'header' => 'Вход',
                        'filter' => false,
                        'format' => 'raw',
                        'visible' => (($currentUser->role == User::ROLE_ADMIN) || ($currentUser->id == 176)) ? true : false,
                        'value' => function ($data) {

                            if(($data->id == 1) || ($data->id == 176)) {
                                return '';
                            } else {

                                if ($data->status == 10) {
                                    return Html::a('Открыт', ['/user/closelogin', 'status' => 0, 'id' => $data->id], ['class' => 'btn btn-danger btn-sm']);
                                } else if ($data->status == 0) {
                                    return Html::a('Закрыт', ['/user/closelogin', 'status' => 10, 'id' => $data->id], ['class' => 'btn btn-success btn-sm']);
                                } else {
                                    return '';
                                }

                            }

                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update}{delete}',
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/user/update', 'id' => $model->id]);
                            },
                        ]
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>