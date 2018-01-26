<?php

/**
 * @var $this yii\web\View
 * @var $modelCompany common\models\Company
 * @var $modelCompanyInfo common\models\CompanyInfo
 * @var $modelCompanyOffer common\models\CompanyOffer
 * @var $admin bool
 */
use common\models\Company;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use common\models\CompanyInfo;
use common\models\Email;

echo $this->render('_modal', [
    'modelCompany' => $modelCompany,
]);

$workTime = $modelCompany->getWorkTimeArray();

// получаем email назначения
$email = '';
$actionLinkEmail = Url::to('@web/email/sendemail');
$actionGetTrack = Url::to('@web/monthly-act/gettrack');
$mailTemplateID = 6;
$tracklink = '';
$trackID = '';

$script = <<< JS
    $('#company-worktime-targ').click(function() {
        $('#everyday').hide();
        $('#anyday').show();
        $('.modaltime').appendTo('form#w23');
        $('.modaltime').show();     
    });
    
    $('input[type="radio"]').on('change', function () {
        var value = $('input[type="radio"]:checked').val();
        if(value == 0){
            $('#everyday').hide();
            $('#anyday').hide();
        } else if(value == 1){
            $('#anyday').hide();
            $('#everyday').show();
        } else if(value == 2){
            $('#everyday').hide();
            $('#anyday').show();
        }
    });
    
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

if($modelCompanyOffer->mail_number) {
    $email = 'Email не указан! ' . Html::a('Указать', Url::to('@web/company/info?id=') . $modelCompanyOffer->company_id, ['target' => 'blank']);

    // Ссылка на отслеживание
    $trackID = $modelCompanyOffer->mail_number;
    $tracklink = 'https://www.pochta.ru/tracking#' . $trackID;

    // Получаем почту назначения
    $modelInfo = CompanyInfo::findOne(['company_id' => $modelCompanyOffer->company_id]);

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
        <?= $modelCompany->name ?>
        <div class="header-btn pull-right">

            <?php if($modelCompany->status != Company::STATUS_TENDER) { ?>
            <?= $modelCompany->status != Company::STATUS_ARCHIVE ?
                Html::a('В архив', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ARCHIVE], ['class' => 'btn btn-success btn-sm']) : '' ?>
            <?= $modelCompany->status != Company::STATUS_REFUSE ? 
                Html::a('В архив 2', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_REFUSE], ['class' => 'btn btn-success btn-sm']) : '' ?>
            <?= $modelCompany->status != Company::STATUS_ARCHIVE3 ?
                Html::a('В архив 3', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ARCHIVE3], ['class' => 'btn btn-success btn-sm']) : '' ?>

            <?= $modelCompany->status != Company::STATUS_ACTIVE ?
                Html::a('В активные', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ACTIVE], ['class' => 'btn btn-success btn-sm']) : '' ?>

            <?php } ?>

            <?= $admin ? Html::a('Удалить', ['company/delete','id' => $modelCompany->id], ['class' => 'btn btn-danger btn-sm']) : ''?>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-bordered list-data">
            <tr>
                <td class="list-label-md"><?= $modelCompany->getAttributeLabel('name') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompany,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'name',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите название'],
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">Адрес организации</td>
                <td>
                    <?php
                    $editable = Editable::begin([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'city',
                        'displayValue' => $modelCompanyInfo->fullAddress,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите город'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    $form = $editable->getForm();
                    echo Html::hiddenInput('kv-complex', '1');
                    $editable->afterInput =
                        $form->field($modelCompanyInfo, 'street') .
                        $form->field($modelCompanyInfo, 'house') .
                        $form->field($modelCompanyInfo, 'index');
                    Editable::end();
                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $modelCompany->getAttributeLabel('car_type') ?></td>
                <td>
                    <?php

                    $resType = "";

                    if($modelCompany->car_type == 0) {
                        $resType = "Грузовой транспорт";
                    } else if($modelCompany->car_type == 1) {
                        $resType = "Легковой транспорт";
                    } else if($modelCompany->car_type == 2) {
                        $resType = "Грузовой и легковой транспорт";
                    }

                    $editableForm = Editable::begin([
                        'model' => $modelCompany,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'car_type',
                        'displayValue' => $resType,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'style' => 'display:none;'],
                        'formOptions' => [
                            'action' => ['/company/update', 'id' => $modelCompany->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);

                    $form = $editableForm->getForm();
                    echo Html::hiddenInput('kv-complex', '1');

                    $editableForm->afterInput = $form->field($modelCompany, 'car_type')->dropDownList([0 => 'Грузовой транспорт', 1 => 'Легковой транспорт', 2 => 'Грузовой и легковой транспорт'], ['class' => 'form-control', 'options'=>[$modelCompany->car_type => ['Selected'=>true]]]) . '';
                    Editable::end();

                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $modelCompanyInfo->getAttributeLabel('comment') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'comment',
                        'displayValue' => $modelCompanyInfo->comment,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">Местное время</td>
                <td>
                    <?php

                    $showTimeLocation = '';
                    $timeCompany = time() + (3600 * $modelCompanyInfo->time_location);

                    if($modelCompanyInfo->time_location == 0) {
                        $showTimeLocation = date('H:i', $timeCompany);
                    } else {
                        if($modelCompanyInfo->time_location > 0) {
                            $showTimeLocation = date('H:i', $timeCompany) . ' (' . '+' . $modelCompanyInfo->time_location . ')';
                        } else {
                            $showTimeLocation = date('H:i', $timeCompany) . ' (' . $modelCompanyInfo->time_location . ')';
                        }
                    }

                    $editableForm = Editable::begin([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'time_location',
                        'displayValue' => $showTimeLocation,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Местное время', 'style' => 'display:none;'],
                        'formOptions' => [
                            'action' => ['/company-info/updatetimelocation', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);

                    $form = $editableForm->getForm();
                    echo Html::hiddenInput('kv-complex', '1');

                    $editableForm->afterInput = '' . $form->field($modelCompanyInfo, 'time_location')->dropDownList(['-12' => -12, '-11' => -11, '-10' => -10, '-9' => -9, '-8' => -8, '-7' => -7, '-6' => -6, '-5' => -5, '-4' => -4, '-3' => -3, '-2' => -2, '-1' => -1, 0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12], ['class' => 'form-control', 'options'=>[$modelCompanyInfo->time_location => ['Selected'=>true]]]) . '';
                    Editable::end();

                    ?>
                </td>
            </tr>
            <?php if($modelCompany->type > 1) { ?>
            <tr>
                <td class="list-label-md"><?= $modelevent->getAttributeLabel('date_from') ?></td>
                <td>

                    <?php

                    if ($modelCompanyInfo->time_location == 0) {
                        $time = time();
                    } else {
                        $time = (time() + (3600 * $modelCompanyInfo->time_location));
                    }
                        if (isset($modelevent)) {
                            if ((isset($modelevent->date_from)) && (isset($modelevent->date_to))) {
                                if (($modelevent->date_from) && ($modelevent->date_to) && ($time > $modelevent->date_to)) {
                                    $modelevent->date_from = '';
                                    $modelevent->date_to = '';
                                    $modelevent->comment = '';
                                    $modelevent->save();
                                }
                            }
                        }

                        echo Editable::widget([
                            'model' => $modelevent,
                            'buttonsTemplate' => '{submit}',
                            'submitButton' => [
                                'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                            ],
                            'attribute' => 'date_from',
                            'displayValue' => $modelevent->date_from ? date('d.m.Y H:i', $modelevent->date_from) : '',
                            'inputType' => Editable::INPUT_DATETIME,
                            'asPopover' => true,
                            'placement' => PopoverX::ALIGN_LEFT,
                            'size' => 'lg',
                            'options' => [
                                'options' => ['value' => $modelevent->date_from ? date('d.m.Y H:i', $modelevent->date_from) : ''],
                                'class' => 'form-control',
                                'removeButton' => false,
                                'pluginOptions' => [
                                    'format' => 'dd.mm.yyyy hh:i',
                                    'weekStart' => 1,
                                    'autoclose' => true,
                                    'pickerPosition' => 'bottom-right',
                                ],
                            ],
                            'formOptions' => [
                                'action' => ['/company-info/commentupdate', 'id' => $modelCompany->id],
                            ],
                            'valueIfNull' => '<span class="text-danger">не задано</span>',
                        ]);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelevent->getAttributeLabel('date_to') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelevent,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'date_to',
                        'displayValue' => $modelevent->date_to ? date('d.m.Y H:i', $modelevent->date_to) : '',
                        'inputType' => Editable::INPUT_DATETIME,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => [
                            'options' => ['value' => $modelevent->date_to ? date('d.m.Y H:i', $modelevent->date_to) : ''],
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy hh:i',
                                'weekStart'=>1,
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                        ],
                        'formOptions' => [
                            'action' => ['/company-info/commentupdate', 'id' => $modelCompany->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $modelevent->getAttributeLabel('comment') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelevent,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'comment',
                        'displayValue' => $modelevent->comment,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                        'formOptions' => [
                            'action' => ['/company-info/commentupdate', 'id' => $modelCompany->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
    <?php } ?>
            <tr>
                <td class="list-label-md">
                    <?= $modelCompany->type == Company::TYPE_WASH ? 'Телефон для записи на мойку' : $modelCompanyInfo->getAttributeLabel('phone') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'phone',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите телефон'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">График работы</td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompany,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i id="graphwork" class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'workTime',
                        'displayValue' => $modelCompany->workTimeHtml,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'inputType' => Editable::INPUT_HIDDEN,
                        'submitOnEnter' => false,
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                            'style' => 'text-align: left',
                            'rows' => 10
                        ],
                        'formOptions' => [
                            'action' => ['/company/update', 'id' => $modelCompany->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                        'footer' => '{buttons}'
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelCompanyOffer->getAttributeLabel('mail_number') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyOffer,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'mail_number',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите номер почтового отделения'],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                    <div class="form-group" style="display: inline">

                            <?php

                            if($trackID) {
                                echo Html::a('Проверить почтовое отправление',
                                    $tracklink,
                                    ['target' => 'blank', 'class' => 'btn btn-primary'])  . '<span class="btn btn-danger btn-sm showModalTracher" style="font-size: 15px; padding:8px 10px 8px 10px; margin-left: 15px;">Отправить уведомление</span>';
                            }

                            ?>

                    </div>
                </td>
            </tr>

            <?php
            if($modelCompany->type == 1) {

                $adVal = str_replace(',,', ',', $modelCompanyInfo->fullAddress);
                $adVal = trim($adVal);
                $adVal = explode(',', $adVal);
                $adVal = $adVal[0];
                $adVal = str_replace(', Город', '', $adVal);
                $adVal = str_replace(', Улица', '', $adVal);
                $adVal = str_replace(', Строение', '', $adVal);
                $adVal = str_replace(', Индекс', '', $adVal);
                $adVal = str_replace(', пос. РТС', '', $adVal);
                $adVal = str_replace(' пос ', '', $adVal);
                $adVal = str_replace(' пос', '', $adVal);
                $adVal = str_replace('пос ', '', $adVal);
                $adVal = str_replace('пос', '', $adVal);
                $adVal = str_replace('?', '', $adVal);

                echo "<tr>
                <td class=\"list-label-md\">Создать коммерческое предложение</td>
                <td>" . Html::a('Создать', [
                        'company/offer',
                        'ad' => $adVal,
                        'type' => 2,
                    ], ['class' => 'btn btn-primary']) . "</td>
            </tr>";
            } else if($modelCompany->type == 3) {

                // Вывод подтипов сервисов

                echo "<tr><td class=\"list-label-md\">Типы сервиса</td><td style='font-size:13px;'>";
                echo Html::beginForm(['company/subtype', 'id' => $modelCompany->id, 'type' => $modelCompany->type], 'post', ['class' => 'uploadPhonesForm']);

                // Получаем список подтипов сервиса
                $arrServiceSubTypes = \backend\controllers\CompanyController::getSubTypes($modelCompany->id);

                foreach (Company::$subTypeService as $key => $value) {
                    echo Html::checkbox('sub_type[' . $key . ']', isset($arrServiceSubTypes[$key]) ? 1 : 0, [
                        'label' => $value['ru'],
                        'labelOptions' => [
                            'class' => 'checkbox-inline',
                            'style' => 'margin-right: 10px;'
                        ]
                    ]);
                }

                echo "<br />" .Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm', 'style' => 'margin-top:10px;']);

                echo Html::endForm();
                echo "</td></tr>";
                // Вывод подтипов сервисов
            }
            ?>

        </table>
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