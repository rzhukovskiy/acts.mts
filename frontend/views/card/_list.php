<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Карты
        <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapseCardList"
             aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть
        </div>
    </div>
    <div class="collapse" id="collapseCardList">
        <div class="panel-body">
            <?php
            Pjax::begin();
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'emptyText' => '',
                'summary' => false,
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'number',
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'options' => [
                            'style' => 'width: 70px',
                        ],
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/card/update', 'id' => $model->id]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/card/delete', 'id' => $model->id], [
                                    'data-confirm' => "Уверены, что хотите удалить эту карту?",
                                    'data-method' => "post",
                                    'data-pjax' => "0",
                                ]);
                            },
                        ]
                    ]
                ],
            ]);
            Pjax::end();
            ?>
        </div>
    </div>
</div>