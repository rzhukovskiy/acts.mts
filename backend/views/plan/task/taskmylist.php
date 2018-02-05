<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\models\User;
use common\models\TaskUser;
use common\models\TaskMy;
use yii\helpers\Url;

$this->title = 'Собственные задачи';

if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 176)) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist'], 'active' => Yii::$app->controller->action->id == 'taskmylist'],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
    ];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist'], 'active' => Yii::$app->controller->action->id == 'taskmylist'],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $tabs,
]);

$GLOBALS['userLists'] = $userLists;

$isAdmin = (\Yii::$app->user->identity->role == User::ROLE_ADMIN) ? 1 : 0;
$taskmystatus = Url::to('@web/plan/taskmystatus');
$taskmypriority = Url::to('@web/plan/taskmypriority');

$script = <<< JS
$('.change-execution_status').change(function(){

    var select=$(this);
    $.ajax({
            url: '$taskmystatus',
            type: "post",
            data: {status:$(this).val(),id:$(this).data('id')},
            success: function(data){
        select.parent().attr('class',data);
        if(($isAdmin!=1)&&(select.data('executionstatus')!=1)){
            select.attr('disabled', 'disabled');
        }
    }
        });
    });
$('.change-priority_status').change(function(){

    var select=$(this);
    $.ajax({
            url: '$taskmypriority',
            type: "post",
            data: {status:$(this).val(),id:$(this).data('id')},
            success: function(data){
        select.parent().attr('class',data);
        if(($isAdmin!=1)&&(select.data('prioritystatus')!=1)){
            select.attr('disabled', 'disabled');
        }
    }
        });
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
    $column = [
        [
            'header' => 'Статус',
            'content' => function ($data) {
                    if (isset($data->priority)) {
                        return TaskUser::$priorityStatus[$data->priority];
                    } else {
                        return '';
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
            'attribute' => 'task',
            'vAlign'=>'middle',
            'format' => 'raw',
            'value' => function ($data) {

                if ($data->task) {
                    return (((isset($data->title)) && (mb_strlen($data->title) > 1)) ? ('<b>Тема: ' . $data->title . '</b><br />') : "") . mb_substr(nl2br($data->task), 0, 300) . (mb_strlen($data->task) > 300 ? ('&nbsp&nbsp<a target="_blank" href="/plan/taskfull?id=' . $data->id . '" style="color: darkred">Подробнее</a>') : '');
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'data',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->data) {
                    return date('d.m.y H:i', $data->data);
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'Осталось<br/> времени',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if (($data->data) && ($data->status !== 2)) {
                    $lostDateText = '';
                    $lostDate = $data->data - time();

                    $days = ((Int) ($lostDate / 86400));
                    $lostDate -= (((Int) ($lostDate / 86400)) * 86400);

                    $hours = (round($lostDate / 3600));
                    $lostDate -= (round($lostDate / 3600) * 3600);

                    $minutes = (round($lostDate / 60));

                    $lostDateText .= 'Дней: ' .  abs($days);
                    $lostDateText .= ', часов: ' . abs($hours);
                    $lostDateText .= ', минут: ' . abs($minutes);

                    if ($data->data > time()) {
                        return '<span style="color: green">' . $lostDateText . '</span>';
                    } else if ($data->data < time()) {
                        return '<span style="color: red">- ' . $lostDateText . '</span>';
                    } else {
                        return $lostDateText;
                    }

                } else if (($data->status == 2) && ($data->data < $data->data_status) && ($data->data)) {
                    return '<span style="color: red">Выполнено не вовремя</span>';
                } else if (($data->status == 2) && ($data->data > $data->data_status)) {
                    return '<span style="color: green">Выполнено вовремя</span>';
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'vAlign'=>'middle',
            'value' => function ($data, $key, $index, $column) {
                return Html::activeDropDownList($data, 'status', TaskUser::$executionStatus,
                    [
                        'class'              => 'form-control change-execution_status',
                        'data-id'            => $data->id,
                        'data-executionStatus' => $data->status,
                        'disabled'           => TaskUser::payDis($data->status) ? 'disabled' : false,
                    ]

                );
            },

            'contentOptions' => function ($data) {
                return [
                    'class' => TaskUser::colorForExecutionStatus($data->status),
                    'style' => 'width: 155px',
                ];
            },
        ],
        [
            'attribute' => 'priority',
            'format' => 'raw',
            'contentOptions' => ['style' => 'min-width: 145px'],
            'vAlign'=> 'middle',
            'value' => function ($data, $key, $index, $column) {
                return Html::activeDropDownList($data, 'priority', TaskUser::$priorityStatus,
                    [
                        'class'              => 'form-control change-priority_status',
                        'data-id'            => $data->id,
                        'data-priorityStatus' => $data->priority,
                    ]

                );
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
                        ['/plan/taskmyfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/plan/taskmydelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                },
            ],
        ],
    ];
} else {
    $column = [
        [
            'header' => 'Статус',
            'content' => function ($data) {
                if (isset($data->priority)) {
                    return TaskUser::$priorityStatus[$data->priority];
                } else {
                    return '';
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
            'attribute' => 'task',
            'filter' => false,
            'format' => 'raw',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->task) {
                    return (((isset($data->title)) && (mb_strlen($data->title) > 1)) ? ('<b>Тема: ' . $data->title . '</b><br />') : "") . mb_substr(nl2br($data->task), 0, 300) . (mb_strlen($data->task) > 300 ? ('&nbsp&nbsp<a target="_blank" href="/plan/taskfull?id=' . $data->id . '" style="color: darkred">Подробнее</a>') : '');
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'from_user',
            'filter' => Html::activeDropDownList($searchModel, 'from_user', TaskMy::find()->innerJoin('user', '`task_my`.`from_user` = `user`.`id`')->select('user.username')->indexBy('from_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->from_user) {
                    return $GLOBALS['userLists'][$data->from_user];
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'data',
            'filter' => false,
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->data) {
                    return date('d.m.y H:i', $data->data);
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'Осталось<br/> времени',
            'vAlign'=>'middle',
            'filter' => false,
            'format'=> 'raw',
            'value' => function ($data) {

                if (($data->data) && ($data->status !== 2)) {
                    $lostDateText = '';
                    $lostDate = $data->data - time();

                    $days = ((Int) ($lostDate / 86400));
                    $lostDate -= (((Int) ($lostDate / 86400)) * 86400);

                    $hours = (round($lostDate / 3600));
                    $lostDate -= (round($lostDate / 3600) * 3600);

                    $minutes = (round($lostDate / 60));

                    $lostDateText .= 'Дней: ' .  abs($days);
                    $lostDateText .= ', часов: ' . abs($hours);
                    $lostDateText .= ', минут: ' . abs($minutes);

                    if ($data->data > time()) {
                        return '<span style="color: green">' . $lostDateText . '</span>';
                    } else if ($data->data < time()) {
                        return '<span style="color: red">- ' . $lostDateText . '</span>';
                    } else {
                        return $lostDateText;
                    }

                } else if (($data->status == 2) && ($data->data < $data->data_status) && ($data->data)) {
                    return '<span style="color: red">Выполнено не вовремя</span>';
                } else if (($data->status == 2) && ($data->data > $data->data_status)) {
                    return '<span style="color: green">Выполнено вовремя</span>';
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'status',
            'filter' => false,
            'format' => 'raw',
            'vAlign'=>'middle',
            'value' => function ($data, $key, $index, $column) {
                return Html::activeDropDownList($data, 'status', TaskUser::$executionStatus,
                    [
                        'class'              => 'form-control change-execution_status',
                        'data-id'            => $data->id,
                        'data-executionStatus' => $data->status,
                        'disabled'           => TaskUser::payDis($data->status) ? 'disabled' : false,
                    ]

                );
            },

            'contentOptions' => function ($data) {
                return [
                    'class' => TaskUser::colorForExecutionStatus($data->status),
                    'style' => 'width: 155px',
                ];
            },
        ],
        [
            'attribute' => 'priority',
            'format' => 'raw',
            'contentOptions' => ['style' => 'min-width: 145px'],
            'vAlign'=> 'middle',
            'value' => function ($data, $key, $index, $column) {
                return Html::activeDropDownList($data, 'priority', TaskUser::$priorityStatus,
                    [
                        'class'              => 'form-control change-priority_status',
                        'data-id'            => $data->id,
                        'data-priorityStatus' => $data->priority,
                    ]

                );
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
                        ['/plan/taskmyfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/plan/taskmydelete', 'id' => $data->id],
                        ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                },
            ],
        ],
    ];
}
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Собственные задачи
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['plan/taskmyadd'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => ($isAdmin == 1) ? $searchModel : NULL,
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
