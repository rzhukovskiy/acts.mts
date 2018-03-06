<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $admin null|bool
 */

use kartik\grid\GridView;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список стоянок
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                'name',
                'address',
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'vAlign'=>'middle',
                    'template' => '{update} {delete}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $data, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                                ['/company/update', 'id' => $data->id]);
                        },
                        'delete' => function ($url, $data, $key) {
                            if (Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>',
                                    ['/company/delete', 'id' => $data->id],
                                    ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                            }

                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>