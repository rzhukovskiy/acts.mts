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
 */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <?= $this->render('_tabs') ?>
    <div class="panel panel-primary">
        <div class="panel-heading">Добавить пользователя</div>
        <div class="panel-body">
            <?= $this->render('_form', [
                'model' => $newUser,
                'companyDropDownData' => $companyDropDownData,
            ]) ?>
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">Пользователи</div>
        <div class="panel-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'summary' => false,
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
                                if ($model->role != \common\models\User::ROLE_ADMIN)
                                    return Html::a('Войти', ['/user/login', 'id' => $model->id], ['class' => 'btn btn-xs btn-default']);
                            },
                        ]
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => function ($url, $model, $key) {
                                $options = [
                                    'title' => Yii::t('yii', 'Delete'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                ];
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/user/delete',
                                    'id' => $model->id,
                                    'type' => $model->company->type],
                                    $options);
                            },
                        ]
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>