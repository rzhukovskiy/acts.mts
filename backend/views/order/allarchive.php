<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */

use common\models\Entry;
use common\models\Company;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use common\assets\CanvasJs\CanvasJsAsset;
use yii\bootstrap\Tabs;
use common\models\Service;

$action = \Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
$serviceList = [Service::TYPE_WASH, Service::TYPE_SERVICE, Service::TYPE_TIRES, Service::TYPE_PARKING];
foreach ($serviceList as $type_id) {
    $items[] = [
        'label' => Service::$listType[$type_id]['ru'],
        'url' => ['/order/archive', 'type' => $type_id],
        'active' => $requestType == $type_id,
    ];
}
$items[] = [
    'label' => 'Общее',
    'url' => ['/order/allarchive'],
    'active' => Yii::$app->controller->action->id == 'allarchive',

];

echo Tabs::widget([
    'items' => $items,
]);

CanvasJsAsset::register($this);

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

$('.phoneBody a').css('cursor', 'pointer');

// Проверка на HTTPS
if (window.location.protocol == 'http:') {
    var toHTTPS = confirm("Для корректной работы звонков через сайт необходимо использовать безопасный протокол связи.");
    
    if(toHTTPS == true) {
        location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
    }
    
}

// Проверка на HTTPS

    // Получаем данные для звонка
    var codeCall, callCipher = '';

    $.ajax({
        type     :'POST',
        cache    : false,
        url  : '/company/getcall',
        success  : function(data) {

            var response = $.parseJSON(data);

            if (response.success == 'true') {

                // Удачно
                codeCall = response.code;
                callCipher = response.cipher;

                userAgent = new SIP.UA({
                    uri: codeCall + '@cc.mtransservice.ru',
                    wsServers: ['wss://cc.mtransservice.ru:7443'],
                    authorizationUser: codeCall,
                    password: callCipher
                });

                options = {
                    media: {
                        constraints: {
                            audio: true,
                            video: false
                        },
                        render: {
                            remote: document.getElementById('remoteVideo'),
                        }
                    }
                };

            } else {
                // Неудачно
            }

        }
    });
    // Получаем данные для звонка
    
// Call Phone
var session;
var audio;
var muteCall = false;
var holdCall = false;
var timerMetaText, timerShowTime;
var statusTimer = 1;
var callTimeNum = 0;
var statusCall = 1;
var muteButtIco = $('.muteCall span');
var holdButtIco = $('.holdCall span');
var callTimer = $('.showCallTimer');
var cancelCall = $('.cancelCall');
var muteCallButt = $('.muteCall');
var holdCallButt = $('.holdCall');

var selNumberCont = '';
var extNumber = '';

function updCallPr() {
    
    if(statusTimer == 0) {
        callTimer.text('Вызов.');
        statusTimer = 1;
    } else if(statusTimer == 1) {
        callTimer.text('Вызов..');
        statusTimer = 2;
    } else if(statusTimer == 2) {
        callTimer.text('Вызов...');
        statusTimer = 3;
    } else {
        callTimer.text('Вызов..');
        statusTimer = 0;
    }

    timerMetaText = setTimeout(updCallPr, 1200);

}

function showTimerCont() {
    
    if(callTimeNum) {
    
    if((new Date - callTimeNum) >= 1000) {
        var timeLost = new Date - callTimeNum;
        
        timeLost = Math.floor(timeLost / 1000);
        var showData = "";

        if((Math.floor(timeLost / 3600)) > 0) {

            if((Math.floor(timeLost / 3600)) > 9) {
                showData = (Math.floor(timeLost / 3600)) + ":";
            } else {
                showData = "0" + (Math.floor(timeLost / 3600)) + ":";
            }

        } else {
            showData = "00:";
        }

        if ((Math.floor(timeLost / 60)) > 0) {

            if ((Math.floor(timeLost / 60)) > 9) {

                if ((Math.floor(timeLost / 60)) < 60) {
                    showData += (Math.floor(timeLost / 60)) + ":";
                } else {
                    showData += "59:";
                }

            } else {
                showData += "0" + (Math.floor(timeLost / 60)) + ":";
            }

        } else {
            showData += "00:";
        }

        var numMin;

        if((Math.floor(timeLost / 60)) > 0) {
            numMin = timeLost - (60 * (Math.floor(timeLost / 60)));
        } else {
            numMin = timeLost;
        }

        if(numMin > 9) {

            if(numMin < 60) {
                showData += numMin;
            } else {
                showData += "59";
            }

        } else {
            showData += "0" + numMin;
        }

        return showData;
        
    } else {
        return '00:00:00';
    }
} else {
        return '00:00:00';
}

}

function updCountTimer() {
    
    callTimer.text(showTimerCont());

    timerShowTime = setTimeout(updCountTimer, 1000);

}

function doCall() {
    
    muteCall = false;
    holdCall = false;
    callTimer.text('Вызов.');
    timerMetaText = setTimeout(updCallPr, 1200);

    session = userAgent.invite('sip:' + selNumberCont + '@cc.mtransservice.ru', options);
    
    var callName = $('#companymember-name-' + selNumber.data("id") + '-targ').text();
    $('.showCallName').text(callName);
    $('.showCallNumber').text(selNumberCont);
    $('#showModalCall').modal('show');

    // Звук гудков
    audio = new Audio();
    audio.controls = true;
    audio.src = '/files/sounds/horn.wav';
    audio.loop = true;
    audio.play();
    
    // Отключаем гудки если трубку подняли
    session.on('accepted', function () {
        
        statusCall = 2;
        
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        statusTimer = 1;
        
        // Засекаем время начала разговора
        callTimer.text('00:00:00');
        callTimeNum = new Date;
        timerShowTime = setTimeout(updCountTimer, 1000);
        
        // добавочный номер
        if(extNumber) {
            if(extNumber.length > 0) {
                session.dtmf(extNumber + '#');
            }
        }
        
    });
    
    // Если звонок завершен
    session.on('cancel', function () {
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        clearTimeout(timerShowTime);
        statusTimer = 1;
        callTimer.text('Звонок завершен (' + showTimerCont() + ')');
        
        cancelCall.text('Позвонить заново');
        statusCall = 0;
        cancelCall.removeClass('btn-danger');
        cancelCall.addClass('btn-success');
        
    });
    
    session.on('bye', function () {
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        clearTimeout(timerShowTime);
        statusTimer = 1;
        callTimer.text('Звонок завершен (' + showTimerCont() + ')');
        
        cancelCall.text('Позвонить заново');
        statusCall = 0;
        cancelCall.removeClass('btn-danger');
        cancelCall.addClass('btn-success');
        
    });
    
    session.on('failed', function () {
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        clearTimeout(timerShowTime);
        statusTimer = 1;
        callTimer.text('Звонок завершен (' + showTimerCont() + ')');
        
        cancelCall.text('Позвонить заново');
        statusCall = 0;
        cancelCall.removeClass('btn-danger');
        cancelCall.addClass('btn-success');
        
    });
    
    session.on('refer', function () {
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        clearTimeout(timerShowTime);
        statusTimer = 1;
        callTimer.text('Звонок завершен (' + showTimerCont() + ')');
        
        cancelCall.text('Позвонить заново');
        statusCall = 0;
        cancelCall.removeClass('btn-danger');
        cancelCall.addClass('btn-success');
        
    });
    // Если звонок завершен
    
}

$('.callNumber').on('click', function() {
    
    cancelCall.text('Завершить звонок');
    statusCall = 1;
    cancelCall.removeClass('btn-success');
    cancelCall.addClass('btn-danger');
    
    selNumber = $(this);
    
    // Получаем номер и добавочный номер
    selNumberCont = selNumber.text().split(':');
    extNumber = selNumberCont[1];
    selNumberCont = selNumberCont[0];
    
    selNumberCont = String(selNumberCont);
    selNumberCont = selNumberCont.replace(' ', '');
    selNumberCont = selNumberCont.replace('+', '');
    selNumberCont = selNumberCont.replace('(', '');
    selNumberCont = selNumberCont.replace(')', '');
    
    extNumber = String(extNumber);
    extNumber = extNumber.replace(' ', '');
    extNumber = extNumber.replace('+', '');
    extNumber = extNumber.replace('(', '');
    extNumber = extNumber.replace(')', '');
    // Получаем номер и добавочный номер
    
    doCall();
});

// Кнопка завершения звонка
cancelCall.on('click', function() {
    
    if(statusCall > 0) {
    
    //$('#showModalCall').modal('hide');
    
    session.terminate();
    callTimer.text('Звонок завершен (' + showTimerCont() + ')');
    callTimeNum = 0;
    
    clearTimeout(timerMetaText);
    clearTimeout(timerShowTime);
    statusTimer = 1;
    
    // Звук завершения вызова
    audio.pause();
    audio.currentTime = 0.0;
    
    var audioClose = new Audio();
    audioClose.controls = true;
    audioClose.src = '/files/sounds/cancel.wav';
    audioClose.loop = false;
    audioClose.play();
    
    cancelCall.text('Позвонить заново');
    statusCall = 0;
    cancelCall.removeClass('btn-danger');
    cancelCall.addClass('btn-success');
    
    } else {
        callTimeNum = 0;
        
        cancelCall.text('Завершить звонок');
        statusCall = 1;
        cancelCall.removeClass('btn-success');
        cancelCall.addClass('btn-danger');
        
        muteButtIco.removeClass('glyphicon glyphicon-volume-off');
        muteButtIco.addClass('glyphicon glyphicon-volume-up');
        muteCallButt.removeClass('btn-success');
        muteCallButt.addClass('btn-warning');
        muteCall = false;
        
        holdButtIco.removeClass('glyphicon glyphicon-pause');
        holdButtIco.addClass('glyphicon glyphicon-play');
        holdCallButt.removeClass('btn-success');
        holdCallButt.addClass('btn-warning');
        holdCall = false;
        
        doCall();
        
    }
    
});

muteCallButt.on('click', function() {
    
    if(statusCall == 2) {
    
    if(muteCall == false) {
        muteButtIco.removeClass('glyphicon glyphicon-volume-up');
        muteButtIco.addClass('glyphicon glyphicon-volume-off');
        $(this).removeClass('btn-warning');
        $(this).addClass('btn-success');
        muteCall = true;
        session.mute();
        
    } else {
        muteButtIco.removeClass('glyphicon glyphicon-volume-off');
        muteButtIco.addClass('glyphicon glyphicon-volume-up');
        $(this).removeClass('btn-success');
        $(this).addClass('btn-warning');
        muteCall = false;
        session.unmute();
        
    }
    
    }

});

holdCallButt.on('click', function() {
    
    if(statusCall == 2) {
    
    if(holdCall == false) {
        holdButtIco.removeClass('glyphicon glyphicon-play');
        holdButtIco.addClass('glyphicon glyphicon-pause');
        $(this).removeClass('btn-warning');
        $(this).addClass('btn-success');
        holdCall = true;
        session.hold();
        
    } else {
        holdButtIco.removeClass('glyphicon glyphicon-pause');
        holdButtIco.addClass('glyphicon glyphicon-play');
        $(this).removeClass('btn-success');
        $(this).addClass('btn-warning');
        holdCall = false;
        session.unhold();
        
    }
    
    }

});

// Завершаем звонок если модальное окно закрыли
$('#showModalCall').on('hidden.bs.modal', function () {
    
    session.terminate();
    
    clearTimeout(timerMetaText);
    clearTimeout(timerShowTime);
    statusTimer = 1;
    
    // Звук завершения вызова
    audio.pause();
    audio.currentTime = 0.0;
    
    if(statusCall == 1) {
    var audioClose = new Audio();
    audioClose.controls = true;
    audioClose.src = '/files/sounds/cancel.wav';
    audioClose.loop = false;
    audioClose.play();
    }
    
    cancelCall.text('Завершить звонок');
    statusCall = 1;
    cancelCall.removeClass('btn-success');
    cancelCall.addClass('btn-danger');

    muteButtIco.removeClass('glyphicon glyphicon-volume-off');
    muteButtIco.addClass('glyphicon glyphicon-volume-up');
    muteCallButt.removeClass('btn-success');
    muteCallButt.addClass('btn-warning');
    muteCall = false;

    holdButtIco.removeClass('glyphicon glyphicon-pause');
    holdButtIco.addClass('glyphicon glyphicon-play');
    holdCallButt.removeClass('btn-success');
    holdCallButt.addClass('btn-warning');
    holdCall = false;
    
});

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

// Фильтр
$halfs = [
    '1е полугодие',
    '2е полугодие'
];
$quarters = [
    '1й квартал',
    '2й квартал',
    '3й квартал',
    '4й квартал',
];
$months = [
    'январь',
    'февраль',
    'март',
    'апрель',
    'май',
    'июнь',
    'июль',
    'август',
    'сентябрь',
    'октябрь',
    'ноябрь',
    'декабрь',
];
$days = [
    1 => '01',
    2 => '02',
    3 => '03',
    4 => '04',
    5 => '05',
    6 => '06',
    7 => '07',
    8 => '08',
    9 => '09',
    10 => '10',
    11 => '11',
    12 => '12',
    13 => '13',
    14 => '14',
    15 => '15',
    16 => '16',
    17 => '17',
    18 => '18',
    19 => '19',
    20 => '20',
    21 => '21',
    22 => '22',
    23 => '23',
    24 => '24',
    25 => '25',
    26 => '26',
    27 => '27',
    28 => '28',
    29 => '29',
    30 => '30',
    31 => '31',
];

$ts1 = strtotime($searchModel->dateFrom);
$ts2 = strtotime($searchModel->dateTo);

$year1 = date('Y', $ts1);
$year2 = date('Y', $ts2);

$month1 = date('m', $ts1);
$month2 = date('m', $ts2);

$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
switch ($diff) {
    case 1:
        $period = 1;
        break;
    case 3:
        $period = 2;
        break;
    case 6:
        $period = 3;
        break;
    case 12:
        $period = 4;
        break;
    case 0:
        // для фильтра по дням
        $period = 5;
        break;
    default:
        $period = 0;
}

$rangeYear = range(date('Y') - 10, date('Y'));
$currentYear = isset($searchModel->dateFrom)
    ? date('Y', strtotime($searchModel->dateFrom))
    : date('Y');

$currentMonth = isset($searchModel->dateFrom)
    ? date('n', strtotime($searchModel->dateFrom))
    : date('n');
$currentMonth--;

$currentDay = isset($searchModel->dateFrom) ? date('j', strtotime($searchModel->dateFrom)) : 1;

$filters = '';
$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, Entry::$periodList, [
    'class' => 'select-period form-control',
    'style' => 'margin-right: 10px;'
]);
$periodForm .= Html::dropDownList('day', $currentDay, $days, [
    'id' => 'day',
    'class' => 'autoinput form-control',
    'style' => $period == 5 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('month', $currentMonth, $months, [
    'id' => 'month',
    'class' => 'autoinput form-control',
    'style' => ($diff == 1) || ($period == 5) ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('half', $currentMonth < 5 ? 0 : 1, $halfs, [
    'id' => 'half',
    'class' => 'autoinput form-control',
    'style' => $diff == 6 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('quarter', floor($currentMonth / 3), $quarters, [
    'id' => 'quarter',
    'class' => 'autoinput form-control',
    'style' => $diff == 3 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('year', array_search($currentYear, $rangeYear), range(date('Y') - 10, date('Y')), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => ($diff && $diff <= 12) || $period == 5 ? '' : 'display:none'
]);

$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo', ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

$filters = 'Выбор периода: ' . $periodForm;
// Фильтр

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
                'filterSelector' => '.ext-filter',
                'resizableColumns' => false,
                'beforeHeader' => [
                    [
                        'columns' => [
                            [
                                'content' => $filters,
                                'options' => [
                                    'style' => 'vertical-align: middle',
                                    'colspan' => 13,
                                    'class' => 'kv-grid-group-filter',
                                ],
                            ]
                        ],
                        'options' => ['class' => 'extend-header'],
                    ],
                    [
                        'columns' => [
                            [
                                'content' => '&nbsp',
                                'options' => [
                                    'colspan' => 13,
                                ]
                            ]
                        ],
                        'options' => ['class' => 'kv-group-header'],
                    ],
                ],
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
                        'header'          => 'Компания',
                        'content'            => function ($data) {
                            if(!empty($data->card->company_id)) {
                                $resName = Company::find()->where(['id' => $data->card->company_id])->select('name')->asArray()->column();
                                return isset($resName[0]) ? $resName[0] : 'error';
                            } else {
                                return 'error';
                            }

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
                        'header'          => 'Партнер',
                        'content'            => function ($data) {
                            return !empty($data->company->name) ? Html::encode($data->company->name) : 'error';
                        },
                    ],
                    [
                        'header'    => 'Телефон',
                        'attribute' => 'phone',
                        'format'         => 'raw',
                        'value'     => function ($model) {
                            return isset($model->phone) ? '<a class="callNumber" data-id="' . $model->id . '" style="cursor: pointer;">' . $model->phone . '</a>' : '-';
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{login}',
                        'buttons' => [
                            'login' => function ($url, $model, $key) {
                                return Html::a('Перенести', [
                                    '/order/list',
                                    'type' => $model->service_type,
                                    'Entry[id]' => $model->id,
                                    'CompanySearch[card_number]' => $model->card_number,
                                    'EntrySearch[day]' => date('d-m-Y', $model->start_at),
                                ], ['class' => 'btn btn-xs btn-default']);
                            },
                        ]
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
                    'created_at' => [
                        'attribute'      => 'created_at',
                        'header'      => 'Дата записи',
                        'value'          => function ($data) {
                            return date('H:i d.m.Y', $data->created_at);
                        },
                        'filter'         => false,
                        'contentOptions' => ['class' => 'value_0'],
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div class="col-sm-12">
        <div id="chart_div" style="width:100%;height:500px;"></div>
        <?php
        $js = "CanvasJS.addColorSet('blue', ['#428bca']);
                
                var dataTable = [];
                var dataTmp = [];
                
                var monthsList = [
                'январь',
                'февраль',
                'март',
                'апрель',
                'май',
                'июнь',
                'июль',
                'август',
                'сентябрь',
                'октябрь',
                'ноябрь',
                'декабрь',
                ];
                
                var titleCanves = '';
                var MetaCanves = '';
            
            switch ('" . $diff . "') {
            case '0':
                    
                    // Выбран день, выводим график по часам
                    titleCanves = 'По часам';
                    MetaCanves = 'час';
                    
                    $('.table tbody tr').each(function (id, value) {
                
                    var index = $(this).find('.value_0').text();
                    
                    var arrIndex = index.split(':');
                    index = parseInt(arrIndex[0]);
                    
                    if(dataTmp[index]) {
                        dataTmp[index]++;
                    } else {
                        dataTmp[index] = 1;
                    }
                    
                    });
                    
                // Преобразуем в нужный формат
                dataTmp.forEach(function (value, key) {
                    dataTable.push({
                    label: key + ':00',
                    y: value,
                    });
                });
                // Преобразуем в нужный формат
                
                break;
            case '1':
                
                // Выбран месяц, выводим график по дням
                    titleCanves = 'По дням';
                    MetaCanves = 'день';
                
                $('.table tbody tr').each(function (id, value) {
                
                    var index = $(this).find('.value_0').text();
                
                var arrIndex = index.split(' ');
                var arrIndex = arrIndex[1].split('.');
                    index = parseInt(arrIndex[0]);
                    
                    if(dataTmp[index]) {
                        dataTmp[index]++;
                    } else {
                        dataTmp[index] = 1;
                    }
                    
                 });
                    
                // Преобразуем в нужный формат
                dataTmp.forEach(function (value, key) {
                    dataTable.push({
                    label: key,
                    y: value,
                    });
                });
                // Преобразуем в нужный формат
                
                break;
            case '3':

                // Выбран квартал выводим график по месяцам
                    titleCanves = 'По кварталу';
                    MetaCanves = 'месяц';
                
                $('.table tbody tr').each(function (id, value) {
                
                    var index = $(this).find('.value_0').text();
                
                var arrIndex = index.split(' ');
                var arrIndex = arrIndex[1].split('.');
                    index = parseInt(arrIndex[1]);
                    
                    if(dataTmp[index]) {
                        dataTmp[index]++;
                    } else {
                        dataTmp[index] = 1;
                    }
                    
                 });
                    
                // Преобразуем в нужный формат
                dataTmp.forEach(function (value, key) {
                    dataTable.push({
                    label: monthsList[key - 1],
                    y: value,
                    });
                });
                // Преобразуем в нужный формат

                break;
            case '6':
                // Выбран пол года выводим график по месяцам
                    titleCanves = 'По полугодию';
                    MetaCanves = 'месяц';
                
                $('.table tbody tr').each(function (id, value) {
                
                    var index = $(this).find('.value_0').text();
                
                var arrIndex = index.split(' ');
                var arrIndex = arrIndex[1].split('.');
                    index = parseInt(arrIndex[1]);
                    
                    if(dataTmp[index]) {
                        dataTmp[index]++;
                    } else {
                        dataTmp[index] = 1;
                    }
                    
                 });
                    
                // Преобразуем в нужный формат
                dataTmp.forEach(function (value, key) {
                    dataTable.push({
                    label: monthsList[key - 1],
                    y: value,
                    });
                });
                // Преобразуем в нужный формат
                break;
            case '12':
                // Выбран год, выводим график по месяцам
                    titleCanves = 'По году';
                    MetaCanves = 'месяц';
                
                $('.table tbody tr').each(function (id, value) {
                
                    var index = $(this).find('.value_0').text();
                
                var arrIndex = index.split(' ');
                var arrIndex = arrIndex[1].split('.');
                    index = parseInt(arrIndex[1]);
                    
                    if(dataTmp[index]) {
                        dataTmp[index]++;
                    } else {
                        dataTmp[index] = 1;
                    }
                    
                 });
                    
                // Преобразуем в нужный формат
                dataTmp.forEach(function (value, key) {
                    dataTable.push({
                    label: monthsList[key - 1],
                    y: value,
                    });
                });
                // Преобразуем в нужный формат
                break;
            default:
                // Выбран период за все время
                    titleCanves = 'За все время';
                    MetaCanves = 'месяц и год';
                
                $('.table tbody tr').each(function (id, value) {
                
                    var index = $(this).find('.value_0').text();
                
                var arrIndex = index.split(' ');
                var arrIndex = arrIndex[1].split('.');
                    index = parseInt(arrIndex[1]).toString() + '.' + parseInt(arrIndex[2]).toString();
                    
                    if(dataTmp[index]) {
                        dataTmp[index]++;
                    } else {
                        dataTmp[index] = 1;
                    }
                    
                 });
                    
                // Преобразуем в нужный формат
                for (key in dataTmp) {
                    if (dataTmp[key]) {
                        var arrKey = key.split('.');
                        var keyM = arrKey[0] - 1;
                        keyM = monthsList[keyM];
                        var keyY = arrKey[1];
               
                        dataTable.push({
                            label: (keyM + ' ' + keyY.toString()),
                            y: dataTmp[key],
                        });
                    }
                }
                // Преобразуем в нужный формат
            }
              
                var max = 0;
                dataTable.forEach(function (value) {
                
                value.y = parseFloat(value.y);
                    if (value.y > max) max = value.y;
                });
                var options = {
                    colorSet: 'blue',
                    dataPointMaxWidth: 40,
                    title: {
                        text: titleCanves,
                        fontColor: '#069',
                        fontSize: 22
                    },
                    subtitles: [
                        {
                            text: 'Записи ТС',
                            horizontalAlign: 'left',
                            fontSize: 14,
                            fontColor: '#069',
                            margin: 20
                        }
                    ],
                    data: [
                        {
                            type: 'column', //change it to line, area, bar, pie, etc
                            dataPoints: dataTable
                        }
                    ],
                    axisX: {
                        title: MetaCanves,
                        titleFontSize: 14,
                        titleFontColor: '#069',
                        titleFontWeight: 'bol',
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        interval: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black'
                    },

                    axisY: {
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        tickThickness: 1,
                        gridThickness: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black',
                        valueFormatString: '### ### ###',
                        maximum: max + 0.1 * max
                    }
                };

                $('#chart_div').CanvasJSChart(options);
                ";
        $this->registerJs($js);
        ?>
    </div>
<?php

// Модальное окно зновонк клиенту
$modalAttach = Modal::begin([
    'header' => '<h4>Звонок клиенту</h4>',
    'id' => 'showModalCall',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonModal', 'style' => 'display:none;'],
    'size'=>'modal-sm',
]);

echo "<div style='font-size: 15px;' align='center'>
<span class='showCallName' style='font-size:23px; color:#484e58; display:block;'></span>
<span class='showCallNumber' style='font-size:21px; color:#549d53; display:block; margin-top: 10px;'></span>
<span class='showCallTimer' style='font-size:17px; color:#676d77; display:block; margin-top: 5px;'></span>
<span class='btn btn-warning muteCall' style='margin-top: 10px;'><span class='glyphicon glyphicon-volume-up' style='font-size: 18px;'></span></span>
<span class='btn btn-warning holdCall' style='margin-top: 10px; margin-left: 5px;'><span class='glyphicon glyphicon-play' style='font-size: 18px;'></span></span>
<span class='btn btn-danger cancelCall' style='margin:15px 0px 10px 0px;'>Завершить звонок</span>
</div>";
Modal::end();
// Модальное окно зновонк клиенту

?>