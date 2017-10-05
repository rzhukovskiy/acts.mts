<?php

/**
 * @var $model \common\models\MonthlyAct
 */

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use common\models\CompanyInfo;
use common\models\User;
use common\models\Email;
use yii\web\View;
use yii\helpers\Url;

// получаем email назначения
$email = '';
$actionLinkEmail = Url::to('@web/email/sendemail');
$actionGetTrack = Url::to('@web/monthly-act/gettrack');
$mailTemplateID = 5;
$tracklink = '';
$trackID = '';

if($model->post_number) {
    $email = 'Email не указан! ' . Html::a('Указать', Url::to('@web/company/info?id=') . $model->client_id, ['target' => 'blank']);

    // Ссылка на отслеживание
    $trackID = $model->post_number;
    $tracklink = 'https://www.pochta.ru/tracking#' . $trackID;

    // Получаем почту назначения
    $modelInfo = CompanyInfo::findOne(['company_id' => $model->client_id]);

    if (isset($modelInfo->email)) {
        if ($modelInfo->email) {
            if (filter_var($modelInfo->email, FILTER_VALIDATE_EMAIL)) {
                $email = $modelInfo->email;
            }
        }
    }

$script = <<< JS

// функция проверки email
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

// Отправка уведомления

// Удаляем ненужную кнопку открыть модальное окно
if($('.hideButtonRemove')) {
$('.hideButtonRemove').remove();
}

var loadTrackInfo = false;

// открываем модальное окно уведомления
$('.showModalTracher').on('click', function(){

                if(loadTrackInfo == false) {
                // Заполняем информацию о трекере в тексте письма
                var emailContent = $('.emailContent');
                var htmlTextCont = emailContent.html();

                $.ajax({
                type     :'GET',
                cache    : true,
                data:'trackID=' + '$trackID',
                url  : '$actionGetTrack',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                    // Удачно
                    htmlTextCont = htmlTextCont.replace('{TRACKLIST}', response.trackCont);
                    emailContent.html(htmlTextCont);
                    loadTrackInfo = true;
                } else {
                // Неудачно
                    htmlTextCont = htmlTextCont.replace('{TRACKLIST}', 'Нет информации по отслеживанию');
                    emailContent.html(htmlTextCont);
                }
                
                }
                });
                }
    
$('#showModalNotific').modal('show');

});

$('#send_track').on('click', function() {
    
// Отправляем уведомление
if(validateEmail('$email')) {

    var arrData = [];
    arrData[0] = ['{TRACKLINK}', '$tracklink'];
    arrData[1] = ['{TRACKLIST}', '$trackID'];
    
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'email=' + '$email' + '&id=' + '$mailTemplateID' + '&data=' + JSON.stringify(arrData),
                url  : '$actionLinkEmail',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                $('#showModalNotific').modal('hide');
                alert('Письмо успешно отправлено');
                } else {
                // Неудачно
                $('#showModalNotific').modal('hide');
                alert('Ошибка при отправке письма');
                }
                
                }
                });
    
} else {
    alert('Указан некорректный Email получателя');
}

    
});

// Запрос клиента по номеру ТС

JS;
    $this->registerJs($script, View::POS_READY);

}

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Детализация акта ' . $model->id ?>
    </div>
    <div class="panel-body">
        <?= \common\widgets\Alert::widget() ?>
        <?php
        $form = ActiveForm::begin([
            'action'      => ['monthly-act/detail', 'id' => $model->id],
            'id'          => 'monthly-act-detail-form',
            'options'     => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template'     => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-4 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'act_comment')->textarea(['class' => 'form-control']) ?>

        <?php /*echo $form->field($model, 'act_send_date')->widget(DatePicker::classname(),
            [
                'type'          => DatePicker::TYPE_INPUT,
                'language'      => 'ru',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'dd-mm-yyyy'
                ],
                'options'       => [
                    'class' => 'form-control',
                    'value' => $model->act_send_date ? $model->act_send_date : '',
                ]
            ])->error(false);

        echo $form->field($model, 'act_client_get_date')->widget(DatePicker::classname(),
            [
                'type'          => DatePicker::TYPE_INPUT,
                'language'      => 'ru',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'dd-mm-yyyy'
                ],
                'options'       => [
                    'class' => 'form-control',
                    'value' => $model->act_client_get_date ? $model->act_client_get_date : '',
                ]
            ])->error(false);*/ ?>

        <?= $form->field($model, 'post_number')->input('text', ['class' => 'form-control'])->label() ?>
        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-6">
                <?php

                if($trackID) {

                    // Кнопки проверка и уведомление о почтовом отправлении
                    echo Html::a('Проверить почтовое отправление', $tracklink, ['target' => 'blank', 'class' => 'btn btn-primary']) . '<span class="btn btn-danger btn-sm showModalTracher" style="font-size: 15px; padding:8px 10px 8px 10px; margin-left: 15px;">Отправить уведомление</span>';
                }

                ?>
            </div>
        </div>

        <?= $form->field($model, 'payment_comment')->textarea(['class' => 'form-control']) ?>

        <?php
        // Предоплата только для администратора
        if(Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            echo $form->field($model, 'prepayment')->input('text', ['class' => 'form-control']);
        }
        ?>

        <?php /*echo $form->field($model, 'act_we_get_date')->widget(DatePicker::classname(),
            [
                'type'          => DatePicker::TYPE_INPUT,
                'language'      => 'ru',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'dd-mm-yyyy'
                ],
                'options'       => [
                    'class' => 'form-control',
                    'value' => $model->act_we_get_date ? $model->act_we_get_date : '',
                ]
            ])->error(false);

        echo $form->field($model, 'payment_estimate_date')->widget(DatePicker::classname(),
            [
                'type'          => DatePicker::TYPE_INPUT,
                'language'      => 'ru',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'dd-mm-yyyy'
                ],
                'options'       => [
                    'class' => 'form-control',
                    'value' => $model->payment_estimate_date ? $model->payment_estimate_date : '',
                ]
            ])->error(false);*/ ?>

        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-6">
                <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>
<?php
if($trackID) {

    // Почтовый шаблон для уведомления
    $emailCont = Email::findOne(['id' => $mailTemplateID]);

    // модальное окно, уведомление о почтовом отправлении
    if (isset($emailCont)) {
        $modal = Modal::begin([
            'header' => '<h4>Отправление уведомления</h4>',
            'id' => 'showModalNotific',
            'toggleButton' => ['label' => 'открыть окно', 'class' => 'btn btn-default hideButtonRemove', 'style' => 'display:none;'],
            'size' => 'modal-lg',
        ]);

        echo "<div style='margin-bottom:15px; font-size:15px; color:#000;'><b>Получатель:</b> $email</div>";

        echo "<div style='margin-top:20px; margin-bottom:20px; font-size:16px;'><b>" . (isset($emailCont->title) ? $emailCont->title : "error") . "</b></div>";

        // Формуриуем текст письма
        $textMail = isset($emailCont->text) ? nl2br($emailCont->text) : "error";
        $textMail = str_replace('{TRACKLINK}', Html::a($tracklink, $tracklink, ['target' => 'blank']), $textMail);

        echo "<div class='emailContent' style='word-wrap: break-word; font-size:13px; color:#000;'>" . $textMail . "</div>";
        echo Html::buttonInput("Отправить уведомление", ['id' => 'send_track', 'class' => 'btn btn-primary', 'style' => 'margin-top:20px; padding:7px 16px 6px 16px;']);

        Modal::end();
    }

}
?>