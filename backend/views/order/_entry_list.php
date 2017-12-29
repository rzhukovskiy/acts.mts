<?php

/**
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */
use common\models\Entry;
use yii\grid\GridView;
use yii\helpers\Html;

$script = <<< JS
    $('.change-status').change(function(){
       
     var select=$(this);
        $.ajax({
            url: "/entry/ajax-status",
            type: "post",
            data: {status:$(this).val(),id:$(this).data('id')},
            success: function(data){
                select.parent().attr('class',data);
            }
        });
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);
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
                        return $model->card ? $model->card->number : $model->card_id;
                    },
                ],
                [
                    'header'    => 'Телефон',
                    'attribute' => 'phone',
                    'format'         => 'raw',
                    'value'     => function ($model) {
                        return isset($model->phone) ? '<a class="callNumber" data-id="' . $model->id . '" style="cursor: pointer;">' . $model->phone . '</a>' : '-';
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
                'status' => [
                    'attribute'      => 'status',
                    'value'          => function ($model) {
                        return Html::activeDropDownList($model,
                            'status',
                            Entry::$listStatus,
                            [
                                'class'   => 'form-control change-status',
                                'data-id' => $model->id,
                                'data-status' => $model->status,
                            ]

                        );
                    },
                    'filter'         => false,
                    'format'         => 'raw',
                    'contentOptions' => function ($model) {
                        return [
                            'class' => Entry::colorForStatus($model->status),
                            'style' => 'min-width: 130px'
                        ];
                    },
                ],
            ],
        ]);
        ?>
    </div>
</div>