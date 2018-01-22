<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\models\User;

$this->title = 'Распределение тендеров';
echo Tabs::widget([
    'items' => [
        ['label' => 'Новые', 'url' => ['company/tenderownerlist?win=1'], 'active' => $win == 1],
        ['label' => 'В работе', 'url' => ['company/tenderownerlist?win=0'], 'active' => $win == 0],
        ['label' => 'Архив', 'url' => ['company/tenderownerlist?win=2'], 'active' => $win == 2],
        ['label' => 'Не взяли', 'url' => ['company/tenderownerlist?win=3'], 'active' => $win == 3],
    ],
]);

if ($win == 1) {
$collumn = [

    [
        'header' => '№',
        'vAlign'=>'middle',
        'class' => 'kartik\grid\SerialColumn'
    ],
    [
        'attribute' => 'text',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->text) {
                return $data->text;
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'link',
        'vAlign'=>'middle',
        'format' => 'raw',
        'value' => function ($data) {

            if (isset($data->link)) {
                if ($data->link) {
                    return Html::a('ссылка', $data->link, ['target' => '_blank']);
                } else {
                    return '-';
                }
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'tender_user',
        'vAlign'=>'middle',
        'format' => 'raw',
        'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
        'header' => 'Ответственный<br />сотрудник',
        'value' => function ($data) {

            if ($data->tender_user == 0) {
                return Html::a('Забрать', ['/company/pickup', 'id' => $data->id, 'tender_user' => Yii::$app->user->identity->id, 'data' => strtotime(date("d-m-Y"))], ['class' => 'btn btn-success btn-sm']);
            } else {
                return '';
            }

        },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Действие',
        'vAlign'=>'middle',
        'template' => '{update}{delete}',
        'contentOptions' => ['style' => 'min-width: 60px'],
        'buttons' => [
            'update' => function ($url, $data, $key) {
                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                    ['/company/tenderownerfull', 'id' => $data->id]);
            },
            'delete' => function ($url, $data, $key) {
                if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company/ownerdelete', 'id' => $data->id],
                    ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                }
            },
        ],
    ],
];
} else if ($win == 3) {
    $collumn = [
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'text',
            'vAlign'=>'middle',
            'header' => 'Текст',
            'value' => function ($data) {

                if ($data->text) {
                    return $data->text;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'reason_not_take',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->reason_not_take) {
                    return $data->reason_not_take;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link',
            'vAlign'=>'middle',
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->link)) {
                    if ($data->link) {
                        return Html::a('ссылка', $data->link, ['target' => '_blank']);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/company/tenderownerfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company/ownerdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
                },
            ],
        ],
    ];
} else {
    $collumn = [
        [
            'header' => 'Имя сотрудника',
            'content' => function ($data) {

                if (isset($data->username)) {
                    return $data->username;
                } else {
                    return '-';
                }
            },
            'group' => true,
            'groupedRow' => true,
            'groupOddCssClass' => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
        ],
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'text',
            'vAlign'=>'middle',
            'header' => 'Текст',
            'value' => function ($data) {

                if ($data->text) {
                    return $data->text;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'data',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->data) {
                    return date('d.m.Y', $data->data);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link',
            'vAlign'=>'middle',
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->link)) {
                    if ($data->link) {
                    return Html::a('ссылка', $data->link, ['target' => '_blank']);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{link}{update}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'link' => function ($url, $data, $key) {
                    if (isset($data->tender_id)) {
                        return Html::a('<span class="glyphicon glyphicon-new-window" style="font-size: 17px;"></span>',
                            ['company/fulltender', 'tender_id' => $data->tender_id]);
                    } else {
                        return '';
                    }
                },
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/company/tenderownerfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company/ownerdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
                },
            ],
        ],
    ];
}
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Распределение тендеров
        <div class="header-btn pull-right">
            <?= Yii::$app->user->identity->role == User::ROLE_ADMIN ? Html::a('Добавить', ['company/tenderowneradd'], ['class' => 'btn btn-success btn-sm']) : '' ?>
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
            'columns' => $collumn,
        ]);
        ?>
    </div>
</div>
