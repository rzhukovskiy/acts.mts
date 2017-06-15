<?php

use common\models\CompanyMember;
use yii\bootstrap\Html;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\bootstrap\Modal;

$script = <<< JS

$('.phoneBody a').css('cursor', 'pointer');

// Обновляем страницу если был изменен номер сотрудника
var numPencil = $(".phoneBody .glyphicon-pencil").length;

$('.phoneBody').bind("DOMSubtreeModified",function(){
    if($(".phoneBody .glyphicon-pencil").length != numPencil) {
      location.reload();
    }
});

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


/* @var $this yii\web\View
 * @var $model CompanyMember
 */
?>
<table class="table table-bordered">
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('name')?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'name[' . $model->id . ']',
                'displayValue' => $model->name,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите фио', 'value' => $model->name],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('position')?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'position[' . $model->id . ']',
                'displayValue' => $model->position,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите должность', 'value' => $model->position],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('phone')?></td>
        <td class="phoneBody">
            <?php foreach (explode(',', $model->phone) as $phone) {
                $phone = trim($phone);
                echo "<a class='callNumber' data-id='" . $model->id . "'>$phone</a><br />";
            } ?>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'phone[' . $model->id . ']',
                'asPopover' => true,
                'displayValue' => isset($model->phone) ? '<span class="glyphicon glyphicon-pencil"></span>' : '',
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите номер телефона', 'value' => $model->phone],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
            <video id="remoteVideo" style="width: 0px; height: 0px;"></video>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('email')?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'email[' . $model->id . ']',
                'displayValue' => $model->email,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите электронную почту', 'value' => $model->email],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
            <?php
            Modal::begin([
                'header' => '<h2>Отправка письма</h2>',
                'toggleButton' => [
                    'tag' => 'a',
                    'label' => '<span class="glyphicon glyphicon-envelope"></span>',
                    'style' => 'cursor: pointer',
                ],
            ]);

            echo $this->render('_mail', [
                'model' => $model,
            ]);

            Modal::end();
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"></td>
        <td>
            <div class="form-group">
                <?= Html::a('Удалить', ['company-member/delete', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </td>
    </tr>
</table>

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
