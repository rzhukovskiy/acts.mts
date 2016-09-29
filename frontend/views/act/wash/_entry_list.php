<?php

/**
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */
use common\components\ArrayHelper;
use yii\grid\GridView;
use yii\helpers\Html;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Записи от Международного Транспортного Сервиса
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $searchModel->search([]),
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'start_at',
                    'value' => function ($model) {
                        return date('H:i', $model->start_at);
                    },
                ],
                [
                    'attribute' => 'end_at',
                    'value' => function ($model) {
                        return date('H:i', $model->end_at);
                    },
                ],
                [
                    'attribute' => 'mark_id',
                    'value' => function ($model) {
                        return $model->mark->name;
                    },
                ],
                'number',
                [
                    'attribute' => 'type_id',
                    'value' => function ($model) {
                        return $model->type->name;
                    },
                ],
                [
                    'header' => 'Услуга',
                    'value' => function ($model, $key, $index, $column) use ($serviceList) {
                        return $model->number
                            ? Html::dropDownList('service', [], ArrayHelper::perMutate($serviceList), ['class' => 'form-control', 'prompt' => 'выберите услугу'])
                            : '';
                    },
                    'format' => 'raw',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{login}',
                    'buttons' => [
                        'login' => function ($url, $model, $key) {
                            return $model->number
                                ? Html::a('Оформить', ['/act/create', 'entry_id' => $model->id], ['class' => 'btn btn-primary'])
                                : '';
                        },
                    ]
                ],
            ],
        ]);
        ?>
    </div>
</div>