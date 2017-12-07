<?php

use kartik\grid\GridView;
use yii\helpers\Html;

?>

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
        'columns' => [
            [
                'header' => '№',
                'vAlign'=>'middle',
                'class' => 'kartik\grid\SerialColumn'
            ],
            [
                'attribute' => 'id',
                'format' => 'raw',
                'vAlign'=>'middle',
            ],
            [
                'attribute' => 'company_name',
                'vAlign'=>'middle',
                'filter' => false,
                'header' => 'Компания',
                'value' => function ($data) {

                    if ($data->company_name) {
                        return $data->company_name;
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'inn',
                'vAlign'=>'middle',
                'header' => 'ИНН',
                'value' => function ($data) {

                    if ($data->inn) {
                        return $data->inn;
                    } else {
                        return '-';
                    }

                },
            ],

            [
                'attribute' => 'city',
                'vAlign'=>'middle',
                'filter' => false,
                'header' => 'Город',
                'value' => function ($data) {

                    if ($data->city) {
                        return $data->city;
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'comment',
                'vAlign'=>'middle',
                'filter' => false,
                'header' => 'Комментарий',
                'value' => function ($data) {

                    if ($data->comment) {
                        return $data->comment;
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
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-search"></span>',
                            ['/company/fulltendermembers', 'id' => $model->id]);
                    },
                ],
            ],
        ],
    ]);
    ?>
</div>
