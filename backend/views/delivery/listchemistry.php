<?php

use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = 'Заказ химии';


    $column = [

        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'wash_name',
            'filter' => false,
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->wash_name) {
                    return $data->wash_name;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'date_send',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->date_send) {
                    return date('d.m.Y', $data->date_send);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'size',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->size) {
                    return $data->size;
                } else {
                    return '-';
                }

            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/delivery/fullchemistry', 'id' => $data->id]);
                },
            ],
        ],
    ];

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Заказ химии для партнеров по дезинфекции
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['delivery/newchemistry'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => $column,
        ]);
        ?>
    </div>
</div>
