<?php

/**
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */
use yii\grid\GridView;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Записанные ТС
    </div>
    <div class="panel-body">
        <?php
        $dataProvider = $searchModel->search([]);
        $dataProvider->query->andWhere(['act_id' => null])->andWhere(['is not', 'type_id', null]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout'       => '{items}',
            'emptyText'    => '',
            'columns'      => [
                [
                    'header' => '№',
                    'class'  => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'start_at',
                    'value'     => function ($model) {
                        return date('H:i', $model->start_at);
                    },
                    'contentOptions' => [
                        'class' => 'entry-time',
                    ]
                ],
                [
                    'header'    => 'Марка ТС',
                    'attribute' => 'mark.name',
                    'value'     => function ($model) {
                        return $model->mark->name;
                    },
                ],
                'number',
                [
                    'header'    => 'Тип ТС',
                    'attribute' => 'type.name',
                    'value'     => function ($model) {
                        return $model->type->name;
                    },
                ],
                [
                    'header'    => 'Карта',
                    'attribute' => 'card.number',
                    'value'     => function ($model) {
                        return $model->card->number;
                    },
                ],
                [
                    'class'          => 'yii\grid\ActionColumn',
                    'template'       => '{update} {delete}',
                    'contentOptions' => ['style' => 'min-width: 80px'],
                    'buttons'        => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                                ['/entry/update', 'id' => $model->id]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>',
                                ['/entry/delete', 'id' => $model->id]);
                        },
                    ]
                ],
            ],
        ]);
        ?>
    </div>
</div>