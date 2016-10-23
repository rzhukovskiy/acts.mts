<?php

use yii\bootstrap\Html;
use yii\grid\GridView;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel common\models\search\CarSearch
 */
\yii\widgets\Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => '{items}',
    'emptyText' => '',
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute'          => 'mark_id',
            'content'            => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : 'error';
            },
            'filter'             => \common\models\Mark::getMarkList(),
            'filterInputOptions' => ['prompt' => 'выберите марку ТС', 'class' => 'form-control']
        ],
        'number',
        [
            'attribute'          => 'type_id',
            'content'            => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : 'error';
            },
            'filter'             => \common\models\Type::getTypeList(),
            'filterInputOptions' => ['prompt' => 'выберите тип ТС', 'class' => 'form-control']
        ],
        [
            'attribute' => 'is_infected',
            'content' => function ($data) {
                return $data->is_infected ? 'да' : 'нет';
            },
            'filter' => false,
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/car/update', 'id' => $model->id]);
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/car/delete', 'id' => $model->id], [
                        'data-confirm' => "Are you sure you want to delete this item?",
                        'data-method' => "post",
                        'data-pjax' => "0",
                    ]);
                },
            ]
        ],
    ],
]);

\yii\widgets\Pjax::end();