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

    // Получаем данные для звонка
    var session;
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

// Звуки
var audio;

// Call Phone
var muteCall = false;
var holdCall = false;
var timerMetaText;
var statusTimer = 1;
var muteButtIco = $('.muteCall span');
var holdButtIco = $('.holdCall span');
var callTimer = $('.showCallTimer');

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

$('.callNumber').on('click', function() {

    muteCall = false;
    holdCall = false;
    callTimer.text('Вызов.');
    timerMetaText = setTimeout(updCallPr, 1200);

    session = userAgent.invite('sip:' + $(this).text() + '@cc.mtransservice.ru', options);

    var callName = $('#companymember-name-' + $(this).data("id") + '-targ').text();
    $('.showCallName').text(callName);
    $('.showCallNumber').text($(this).text());
    $('#showModalCall').modal('show');

    // Звук гудков
    audio = new Audio();
    audio.controls = true;
    audio.src = '/files/sounds/horn.wav';
    audio.loop = true;
    audio.play();
    
    // Отключаем гудки если трубку подняли
    session.on('accepted', function () {
        alert(1);
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        statusTimer = 1;
        callTimer.text('00:00:00');
        
    });
    
    // Если звонок завершен
    session.on('cancel', function () {
        alert(2);
        audio.pause();
        audio.currentTime = 0.0;
        
        clearTimeout(timerMetaText);
        statusTimer = 1;
        callTimer.text('Звонок завершен'); 
        
    });

});

// Кнопка завершения звонка
$('.cancelCall').on('click', function() {
    $('#showModalCall').modal('hide');
});

$('.muteCall').on('click', function() {
    
    if(muteCall == false) {
        muteButtIco.removeClass('glyphicon glyphicon-volume-up');
        muteButtIco.addClass('glyphicon glyphicon-volume-off');
        $(this).removeClass('btn-warning');
        $(this).addClass('btn-success');
        muteCall = true;
        session.mute();
        
        clearTimeout(timerMetaText);
        statusTimer = 1;
        callTimer.text('Микрофон выкл.');
        
    } else {
        muteButtIco.removeClass('glyphicon glyphicon-volume-off');
        muteButtIco.addClass('glyphicon glyphicon-volume-up');
        $(this).removeClass('btn-success');
        $(this).addClass('btn-warning');
        muteCall = false;
        session.unmute();
        
        callTimer.text('00:00:00');
        
    }

});

$('.holdCall').on('click', function() {
    
    if(holdCall == false) {
        holdButtIco.removeClass('glyphicon glyphicon-play');
        holdButtIco.addClass('glyphicon glyphicon-pause');
        $(this).removeClass('btn-warning');
        $(this).addClass('btn-success');
        holdCall = true;
        session.hold();
        
        clearTimeout(timerMetaText);
        statusTimer = 1;
        callTimer.text('Звонок на паузе');
        
    } else {
        holdButtIco.removeClass('glyphicon glyphicon-pause');
        holdButtIco.addClass('glyphicon glyphicon-play');
        $(this).removeClass('btn-success');
        $(this).addClass('btn-warning');
        holdCall = false;
        session.unhold();
        
        callTimer.text('00:00:00');
        
    }

});

// Завершаем звонок если модальное окно закрыли
$('#showModalCall').on('hidden.bs.modal', function () {
    session.terminate();
    
    clearTimeout(timerMetaText);
    statusTimer = 1;
    
    // Звук завершения вызова
    audio.pause();
    audio.currentTime = 0.0;
    
    var audioClose = new Audio();
    audioClose.controls = true;
    audioClose.src = '/files/sounds/cancel.wav';
    audioClose.loop = false;
    audioClose.play();
    
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
                $code = Yii::$app->user->identity->code;
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


// Модальное окно показать все вложения
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
// Модальное окно показать все вложения

?>
