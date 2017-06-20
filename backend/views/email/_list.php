<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\Company;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
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
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                'name',
                [
                    'attribute' => 'type',
                    'value' => function ($data) {
                        if($data->type == 0) {
                            return 'Все компании';
                        } else {
                            return Company::$listType[$data->type]['ru'];
                        }
                    },
                ],
                'title',
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{update}{delete}',
                    'contentOptions' => ['style' => 'min-width: 90px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                                ['/email/update', 'id' => $model->id]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon glyphicon-trash" style="margin-left:10px;"></span>',
                                ['/email/delete', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);

        ?>

    </div>

</div>