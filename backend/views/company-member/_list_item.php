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
                            video: true
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
$('.callNumber').on('click', function() {
session = userAgent.invite('sip:' + $(this).text() + '@cc.mtransservice.ru', options);
$('.showCallNumber').text($(this).text());
$('#showModalCall').modal('show');
});

// Кнопка завершения звонка
$('.cancelCall').on('click', function() {
    session.terminate();
    $('#showModalCall').modal('hide');
});

// Завершаем звонок если модальное окно закрыли
$('#showModalCall').on('hidden.bs.modal', function () {
    session.terminate();
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
                echo "<a class='callNumber'>$phone</a><br />";
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
<span class='showCallNumber' style='font-size:27px; color:#767d87;'></span>
<span class='btn btn-danger cancelCall' style='margin-top:10px;'>Завершить звонок</span>
</div>";
Modal::end();
// Модальное окно показать все вложения

?>
