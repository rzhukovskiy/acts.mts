<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $newUser \common\models\User
 * @var $admin boolean
 */

$this->title = 'Пользователи';

?>
<div class="user-index">
    <?= $this->render('_tabs') ?>
    <?php if ($admin) { ?>
        <div class="panel panel-primary">
            <div class="panel-heading">Добавить пользователя</div>
            <div class="panel-body">
                <?= $this->render('_form', [
                    'model' => $newUser,
                    'companyDropDownData' => $companyDropDownData,
                ]) ?>
            </div>
        </div>
    <?php } ?>
    <div class="panel panel-primary">
        <div class="panel-heading">Пользователи</div>
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
                            return Html::a($data->username, ['update', 'id' => $data->id, 'type' => $data->company->type]);
                        }
                    ],
                    [
                        'attribute' => 'company_id',
                        'content' => function ($data) {
                            return $data->company->name;
                        },
                        'filter' => Html::activeDropDownList(
                            $searchModel,
                            'company_id',
                            $companyDropDownData,
                            [
                                'class' => 'form-control',
                                'prompt' => 'Все компании'
                            ]
                        ),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{login}',
                        'buttons' => [
                            'login' => function ($url, $model, $key) {
                                if ($model->role != \common\models\User::ROLE_ADMIN) {
                                    return Html::a('Войти',
                                        ['/user/login', 'id' => $model->id],
                                        ['class' => 'btn btn-xs btn-default']);
                                }
                            },
                        ],
                        'visible' => $admin
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update}{delete}',
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                                    ['/user/update', 'id' => $model->id, 'type' => $model->company->type]);
                            },
                        ],
                        'visible' => $admin
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>