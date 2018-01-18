<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\models\User;
use common\models\TaskUserLink;
use common\models\TaskUser;
use yii\helpers\Url;

$this->title = 'Задачи для пользователей';

if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0'], 'active' => $type == 0],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1'], 'active' => $type == 1],
];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1'], 'active' => $type == 1],
        ['label' => 'Мне поставили задачу', 'url' => ['plan/tasklist?type=2'], 'active' => $type == 2],
    ];
}

echo Tabs::widget([
    'items' => $tabs,
]);

$GLOBALS['usersList'] = $userList;

$isAdmin = (\Yii::$app->user->identity->role == User::ROLE_ADMIN) ? 1 : 0;
$ajaxexecutionstatus = Url::to('@web/plan/ajaxexecutionstatus');

$script = <<< JS
$('.change-execution_status').change(function(){

    var select=$(this);
    $.ajax({
            url: '$ajaxexecutionstatus',
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
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

if ($type == 1) {
    $column = [

        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'task',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->task) {
                    return $data->task;
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'Кому',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->for_user) {
                    return $GLOBALS['usersList'][$data->for_user];
                } else {
                    return '-';
                }
            },
        ],
        [
            'header' => 'Копия',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                $user = TaskUserLink::find()->innerJoin('user', '`user`.`id` = `task_user_link`.`for_user_copy`')->where(['task_id' => $data->id])->select('username')->asArray()->all();
                $alluser = '';

                for ($i = 0; $i < count($user); $i++) {
                    $alluser .= $user[$i]['username'] . '<br/>';
                }

                return $alluser;
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
            'header' => 'Осталось<br/> до истечения',
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

                } else if (($data->status == 2) && ($data->data < $data->data_status)) {
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
                        'disabled'           =>  Yii::$app->user->identity->role !== User::ROLE_ADMIN ? 'disabled' : false,
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
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/plan/taskfull', 'id' => $data->id]);
                },
            ],
        ],
    ];
} else if ($type == 2) {
    $column = [

        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'task',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->task) {
                    return $data->task;
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'От кого',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->from_user) {
                    return $GLOBALS['usersList'][$data->from_user];
                } else {
                    return '-';
                }
            },
        ],
        [
            'header' => 'Ответственный<br/> сотрудник',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->for_user) {
                    if ($data->for_user == Yii::$app->user->identity->id) {
                        return '<b>Вы</b>';
                    } else {
                        return $GLOBALS['usersList'][$data->for_user];
                    }
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
            'header' => 'Осталось<br/> до истечения',
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

                } else if (($data->status == 2) && ($data->data < $data->data_status)) {
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
                        'disabled'           => ((TaskUser::payDis($data->status)) || (Yii::$app->user->identity->id !== $data->for_user)) ? 'disabled' : false,
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
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/plan/taskfull', 'id' => $data->id]);
                },
            ],
        ],
    ];
} else if ($type == 0) {
    $column = [

        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'task',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->task) {
                    return $data->task;
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'Кому',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->for_user) {
                    return $GLOBALS['usersList'][$data->for_user];
                } else {
                    return '-';
                }
            },
        ],
        [
            'header' => 'Копия',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                $user = TaskUserLink::find()->innerJoin('user', '`user`.`id` = `task_user_link`.`for_user_copy`')->where(['task_id' => $data->id])->select('username')->asArray()->all();
                $alluser = '';

                for ($i = 0; $i < count($user); $i++) {
                    $alluser .= $user[$i]['username'] . '<br/>';
                }

                return $alluser;
            },
        ],
        [
            'header' => 'От кого',
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->from_user) {
                    return $GLOBALS['usersList'][$data->from_user];
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
            'header' => 'Осталось<br/> до истечения',
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

                } else if (($data->status == 2) && ($data->data < $data->data_status)) {
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
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/plan/taskfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/plan/taskdelete', 'id' => $data->id],
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
        Задачи для пользователей
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['plan/taskadd'], ['class' => 'btn btn-success btn-sm']) ?>
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
