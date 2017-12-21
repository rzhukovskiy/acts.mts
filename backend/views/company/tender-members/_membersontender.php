<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\TenderLinks;

?>

<div class="panel-body">
    <?php

    $GLOBALS['tender_id'] = Yii::$app->request->get('id');

    // Определяем победителя тендера
    $resWinner = TenderLinks::find()->where(['AND', ['tender_id' => $GLOBALS['tender_id']], ['winner' => 1]])->select('member_id')->asArray()->column();

    $GLOBALS['tender_win'] = 0;
    if(count($resWinner) > 0) {
        $GLOBALS['tender_win'] = $resWinner[0];
    }

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'hover' => false,
        'striped' => false,
        'export' => false,
        'summary' => false,
        'emptyText' => '',
        'layout' => '{items}',
        'rowOptions' => function ($model) {

            // Выделяем цветом для каких типов
            if(isset($GLOBALS['tender_win'])) {
                if ($GLOBALS['tender_win'] == $model->id) {
                    return ['style' => 'background:#ffd5d5;'];
                } else {
                    return '';
                }
            } else {
                return '';
            }
        },
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
                'header' => 'Выиграл',
                'vAlign'=>'middle',
                'template' => '{view}',
                'contentOptions' => ['style' => 'min-width: 60px'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        if ($GLOBALS['tender_win'] == $model->id) {
                            return Html::a('<span class="glyphicon glyphicon-minus"></span>', ['/company/tendermemberwin', 'tender_id' => $GLOBALS['tender_id'], 'member_id' => $model->id, 'winner' => 0]);
                        } else {
                            return Html::a('<span class="glyphicon glyphicon-plus"></span>', ['/company/tendermemberwin', 'tender_id' => $GLOBALS['tender_id'], 'member_id' => $model->id, 'winner' => 1]);
                        }
                    },
                ],

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
