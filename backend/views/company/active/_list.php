<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $userList User[]
 */
use common\models\Company;
use kartik\grid\GridView;
use common\models\User;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', [
                'company/create',
                'Company[type]' => $searchModel->type,
                'Company[status]' => Company::STATUS_ACTIVE
            ], ['class' => 'btn btn-danger btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'address',
                    'group' => true,
                    'groupedRow' => true,
                    'groupOddCssClass' => 'kv-group-header',
                    'groupEvenCssClass' => 'kv-group-header',
                ],
                [
                    'header' => 'Организация',
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'fullAddress',
                    'content'   => function ($data) {
                        return ($data->fullAddress) ? $data->fullAddress : 'не задан';
                    }
                ],
                [
                    'attribute' => 'email',
                    'content' => function ($data) {

                        if(isset($data->info->email)) {
                            if($data->info->email) {
                                return $data->info->email;
                            } else {
                                return 'не задан';
                            }
                        } else {
                            return 'не задан';
                        }

                    },
                    'filter' => true,
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/update', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>