<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
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
        Архив записей
    </div>
    <div class="panel-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
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
                ],
                'number',
                [
                    'attribute'          => 'type_id',
                    'content'            => function ($data) {
                        return !empty($data->type->name) ? Html::encode($data->type->name) : 'error';
                    },
                ],
                [
                    'attribute'          => 'card_id',
                    'content'            => function ($data) {
                        return !empty($data->card->number) ? Html::encode($data->card->number) : 'error';
                    },
                ],
                [
                    'attribute'          => 'start_at',
                    'value'     => function ($model) {
                        return date('H:i', $model->start_at);
                    },
                    'contentOptions' => [
                        'class' => 'entry-time',
                    ]
                ],
                [
                    'attribute'          => 'user_id',
                    'content'            => function ($data) {
                        return !empty($data->user->username) ? Html::encode($data->user->username) : 'нет';
                    },
                ],
                [
                    'attribute'          => 'company_id',
                    'content'            => function ($data) {
                        return !empty($data->company->name) ? Html::encode($data->company->name) : 'error';
                    },
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
        ]); ?>
    </div>
</div>