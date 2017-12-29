<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */

use common\models\Entry;
use common\models\Company;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;

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
                ],
            ],
        ]); ?>
    </div>
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