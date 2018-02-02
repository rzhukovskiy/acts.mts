<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\models\User;
use common\models\TaskUserLink;
use common\models\TaskUser;
use yii\helpers\Url;

$this->title = 'Задачи для пользователей';

if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 176)) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0'], 'active' => $type == 0],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1'], 'active' => $type == 1],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2'], 'active' => $type == 2],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3'], 'active' => $type == 3],
];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1'], 'active' => $type == 1],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2'], 'active' => $type == 2],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3'], 'active' => $type == 3],
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $tabs,
]);

$GLOBALS['usersList'] = $userList;

$isAdmin = (\Yii::$app->user->identity->role == User::ROLE_ADMIN) ? 1 : 0;
$ajaxexecutionstatus = Url::to('@web/plan/ajaxexecutionstatus');
$actionLinkGetComments = Url::to('@web/plan/getcomments');
$taskpriority = Url::to('@web/plan/taskpriority');

$css = "#previewStatus {
background:#fff;
padding:12px;
position:fixed;
font-size:14px;
z-index:50;
border-radius:3px;
border:1px solid #069;
}

.showStatus:hover {
cursor:pointer;
}
";
$this->registerCss($css);

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

$('.change-priority_status').change(function(){

    var select=$(this);
    $.ajax({
            url: '$taskpriority',
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

// При наведении на название показывается статус актов
var margTop = 0;
var margLeft = 0;
var openWindowComm = false;

var arrRessComm = [];

var showStatusVar = $(".showStatus");
    showStatusVar.hover(function() {
        
        if(openWindowComm == false) {
        
            if($("#previewStatus")) {
                $("#previewStatus").remove(); 
            }
            
            openWindowComm = true;
            
                if($(this).parent().data('task') > 0) {
        
                var idKey = $(this).parent().data('task');
                if(typeof(arrRessComm[idKey]) != "undefined" && arrRessComm[idKey] !== null) {
            this.t = this.title;
            this.title = "";

            margTop = window.event.clientY - 20;
            margLeft = window.event.clientX + document.body.scrollLeft + 25;
            
            $("#previewStatus").css("top", margTop + "px")
            .css("left", margLeft + "px")
            .fadeIn("fast");
                    $("body").append("<p id='previewStatus'>" + arrRessComm[idKey] + "</p>");

                openWindowComm = false;
                } else {
                
                    $("body").append("<p id='previewStatus'></p>");
                    
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + idKey,
                url  : '$actionLinkGetComments',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                arrRessComm[idKey] = response.comment;
                
                if($("#previewStatus")) {
                $("#previewStatus").html(arrRessComm[idKey]);
                }
                openWindowComm = false;
                } else {
                // Неудачно
                openWindowComm = false;
                }
                
                }
                });
                
                }
        
            this.t = this.title;
            this.title = "";
            
            if($("#previewStatus")) {
                $("#previewStatus").remove(); 
            }
            
            if(typeof(arrRessComm[idKey]) != "undefined" && arrRessComm[idKey] !== null) {
                $("body").append("<p id='previewStatus'>" + arrRessComm[idKey] + "</p>");
            } else {
                $("body").append("<p id='previewStatus'><u style='color:#757575;'>Комментарий ответственного:</u></p>");
            }

            margTop = window.event.clientY - 20;
            margLeft = window.event.clientX + document.body.scrollLeft + 25;
            
            $("#previewStatus").css("top", margTop + "px")
            .css("left", margLeft + "px")
            .fadeIn("fast");
                
                } else {
                    openWindowComm = false;
                }
            }
        },
        function() {
        if(openWindowComm == false) {
            $("#previewStatus").remove();
            margTop = 0;
            margLeft = 0;
            }
        });
    
    showStatusVar.mousemove(function(e) {
        margTop = window.event.clientY - 20;
        margLeft = window.event.clientX + document.body.scrollLeft + 25;
        $("#previewStatus")
            .css("top", margTop + "px")
            .css("left", margLeft + "px");
    });
// При наведении на название показывается статус актов

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

if ($type == 1) {
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
            'vAlign'=>'middle',
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->task) {
                    return '<span class="showStatus">' . (((isset($data->title)) && (mb_strlen($data->title) > 1)) ? ('<b>Тема: ' . $data->title . '</b><br />') : "") . mb_substr(nl2br($data->task), 0, 300) . '</span>' . (mb_strlen($data->task) > 300 ? ('&nbsp&nbsp<a target="_blank" href="/plan/taskfull?id=' . $data->id . '" style="color: darkred">Подробнее</a>') : '');
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($model, $key, $index, $column){
                 return ['data-task' => $model->id];
            },
        ],
        [
            'header' => 'Кому',
            'filter' => Html::activeDropDownList($searchModel, 'for_user', TaskUser::find()->innerJoin('user', '`task_user`.`for_user` = `user`.`id`')->andWhere(['task_user.from_user' => Yii::$app->user->identity->id])->select('user.username')->indexBy('for_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
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
            'filter' => false,
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
            'filter' => Html::activeDropDownList($searchModel, 'status', TaskUser::$executionStatus, ['class' => 'form-control', 'prompt' => 'Все статусы']),
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
                        'disabled'           =>  (Yii::$app->user->identity->role == User::ROLE_ADMIN || $data->from_user == Yii::$app->user->identity->id)? false : 'disabled',
                    ]

                );
            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}{archive}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search" style="margin-right: 5px;"> </span>',
                        ['/plan/taskfull', 'id' => $data->id]);
                },
                'archive' => function ($url, $data, $key) {
                    if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || ($data->from_user == Yii::$app->user->identity->id)) {
                        return Html::a('<span class="glyphicon glyphicon-folder-open"> </span>', ['/plan/isarchive', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите перенести в архив?"]);
                    }
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"> </span>', ['/plan/taskdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
                },
            ],
        ],
    ];
} else if ($type == 2) {
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
            'filter' => false,
            'value' => function ($data) {

                if ($data->task) {
                    return '<span class="showStatus">' . (((isset($data->title)) && (mb_strlen($data->title) > 1)) ? ('<b>Тема: ' . $data->title . '</b><br />') : "") . mb_substr(nl2br($data->task), 0, 300) . '</span>' . (mb_strlen($data->task) > 300 ? ('&nbsp&nbsp<a target="_blank" href="/plan/taskfull?id=' . $data->id . '" style="color: darkred">Подробнее</a>') : '');
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($model, $key, $index, $column){
                return ['data-task' => $model->id];
            },
        ],
        [
            'header' => 'От кого',
            'vAlign'=>'middle',
            'filter' => Html::activeDropDownList($searchModel, 'from_user', TaskUser::find()->innerJoin('user', '`task_user`.`from_user` = `user`.`id`')->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->select('user.username')->indexBy('from_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
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
            'filter' => Html::activeDropDownList($searchModel, 'for_user', TaskUser::find()->innerJoin('user', '`task_user`.`for_user` = `user`.`id`')->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->select('user.username')->indexBy('for_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
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
            'filter' => false,
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
            'filter' => Html::activeDropDownList($searchModel, 'status', TaskUser::$executionStatus, ['class' => 'form-control', 'prompt' => 'Все статусы']),
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
            'template' => '{update}{archive}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search" style="margin-right: 5px;"> </span>',
                        ['/plan/taskfull', 'id' => $data->id]);
                },
                'archive' => function ($url, $data, $key) {
                    if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || ($data->from_user == Yii::$app->user->identity->id)) {
                        return Html::a('<span class="glyphicon glyphicon-folder-open"> </span>', ['/plan/isarchive', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите перенести в архив?"]);
                    }
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"> </span>', ['/plan/taskdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
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
            'format' => 'raw',
            'filter' => false,
            'value' => function ($data) {

                if ($data->task) {
                    return '<span class="showStatus">' . (((isset($data->title)) && (mb_strlen($data->title) > 1)) ? ('<b>Тема: ' . $data->title . '</b><br />') : "") . mb_substr(nl2br($data->task), 0, 300) . '</span>' . (mb_strlen($data->task) > 300 ? ('&nbsp&nbsp<a target="_blank" href="/plan/taskfull?id=' . $data->id . '" style="color: darkred">Подробнее</a>') : '');
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($model, $key, $index, $column){
                return ['data-task' => $model->id];
            },
        ],
        [
            'header' => 'От кого',
            'vAlign'=>'middle',
            'filter' => Html::activeDropDownList($searchModel, 'from_user', TaskUser::find()->innerJoin('user', '`task_user`.`from_user` = `user`.`id`')->select('user.username')->indexBy('from_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
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
            'header' => 'Кому',
            'vAlign'=>'middle',
            'filter' => Html::activeDropDownList($searchModel, 'for_user', TaskUser::find()->innerJoin('user', '`task_user`.`for_user` = `user`.`id`')->select('user.username')->indexBy('for_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
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
            'filter' => false,
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
            'filter' => false,
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
            'filter' => Html::activeDropDownList($searchModel, 'status', TaskUser::$executionStatus, ['class' => 'form-control', 'prompt' => 'Все статусы']),
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
            'template' => '{update}{archive}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search" style="margin-right: 5px;"> </span>',
                        ['/plan/taskfull', 'id' => $data->id]);
                },
                'archive' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-folder-open"> </span>', ['/plan/isarchive', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите перенести в архив?"]);
                    }
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"> </span>', ['/plan/taskdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
                },
            ],
        ],
    ];
} else if ($type == 3) {
    $column = [

        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'task',
            'vAlign'=>'middle',
            'format' => 'raw',
            'filter' => false,
            'value' => function ($data) {

                if ($data->task) {
                    return '<span class="showStatus">' . (((isset($data->title)) && (mb_strlen($data->title) > 1)) ? ('<b>Тема: ' . $data->title . '</b><br />') : "") . mb_substr(nl2br($data->task), 0, 300) . '</span>' . (mb_strlen($data->task) > 300 ? ('&nbsp&nbsp<a target="_blank" href="/plan/taskfull?id=' . $data->id . '" style="color: darkred">Подробнее</a>') : '');
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($model, $key, $index, $column){
                return ['data-task' => $model->id];
            },
        ],
        [
            'header' => 'От кого',
            'vAlign'=>'middle',
            'filter' => Html::activeDropDownList($searchModel, 'from_user', (Yii::$app->user->identity->role == User::ROLE_ADMIN) ? TaskUser::find()->innerJoin('user', '`task_user`.`from_user` = `user`.`id`')->andwhere(['task_user.is_archive' => 1])->select('user.username')->indexBy('from_user')->orderBy('username ASC')->column() : TaskUser::find()->innerJoin('user', '`task_user`.`from_user` = `user`.`id`')->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andwhere(['task_user.is_archive' => 1])->select('user.username')->indexBy('from_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->from_user) {
                    if ($data->from_user == Yii::$app->user->identity->id) {
                        return '<b>От вас</b>';
                    } else {
                        return $GLOBALS['usersList'][$data->from_user];
                    }
                } else {
                        return '-';
                }
            },
        ],
        [
            'header' => 'Кому',
            'vAlign'=>'middle',
            'filter' => Html::activeDropDownList($searchModel, 'for_user',(Yii::$app->user->identity->role == User::ROLE_ADMIN) ? TaskUser::find()->innerJoin('user', '`task_user`.`for_user` = `user`.`id`')->andwhere(['task_user.is_archive' => 1])->select('user.username')->indexBy('for_user')->orderBy('username ASC')->column() : TaskUser::find()->innerJoin('user', '`task_user`.`for_user` = `user`.`id`')->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andwhere(['task_user.is_archive' => 1])->select('user.username')->indexBy('for_user')->orderBy('username ASC')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
            'format'=> 'raw',
            'value' => function ($data) {

                if ($data->for_user) {
                    if ($data->for_user == Yii::$app->user->identity->id) {
                        return '<b>Вам</b>';
                    } else {
                        return $GLOBALS['usersList'][$data->for_user];
                    }
                } else {
                    return '-';
                }
            },
        ],
        [
            'header' => 'Копия',
            'vAlign'=>'middle',
            'filter' => false,
            'format'=> 'raw',
            'value' => function ($data) {

                $user = TaskUserLink::find()->innerJoin('user', '`user`.`id` = `task_user_link`.`for_user_copy`')->where(['task_id' => $data->id])->select('user.id, user.username')->asArray()->all();
                $alluser = '';
                for ($i = 0; $i < count($user); $i++) {
                    if ($user[$i]['id'] == Yii::$app->user->identity->id) {
                        $alluser .= '<b>Вам</b><br/>';
                    } else {
                        $alluser .= $user[$i]['username'] . '<br/>';
                    }
                }

                return $alluser;
            },
        ],
        [
            'attribute' => 'data',
            'vAlign'=>'middle',
            'filter' => false,
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
            'filter' => Html::activeDropDownList($searchModel, 'status', TaskUser::$executionStatus, ['class' => 'form-control', 'prompt' => 'Все статусы']),
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
            <?= ($type !=2) ? Html::a('Добавить', ['plan/taskadd'], ['class' => 'btn btn-success btn-sm']) : '' ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
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
