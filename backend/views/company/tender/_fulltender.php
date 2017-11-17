<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\helpers\Html;
use yii\web\View;
use common\models\Company;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use common\models\TenderLists;

// массив списков
$arrayTenderList = TenderLists::find()->select('id, description, type')->orderBy('type, id')->asArray()->all();

$arrLists = [];
$oldType = -1;
$tmpArray = [];

for ($i = 0; $i < count($arrayTenderList); $i++) {

    if($arrayTenderList[$i]['type'] == $oldType) {

        $index = $arrayTenderList[$i]['id'];
        $tmpArray[$index] = $arrayTenderList[$i]['description'];

    } else {

        if($i > 0) {

            $arrLists[$oldType] = $tmpArray;
            $tmpArray = [];

            $oldType = $arrayTenderList[$i]['type'];

            $index = $arrayTenderList[$i]['id'];
            $tmpArray[$index] = $arrayTenderList[$i]['description'];

        } else {
            $oldType = $arrayTenderList[$i]['type'];
            $tmpArray = [];

            $index = $arrayTenderList[$i]['id'];
            $tmpArray[$index] = $arrayTenderList[$i]['description'];
        }
    }

    if(($i + 1) == count($arrayTenderList)) {
        $arrLists[$oldType] = $tmpArray;
    }

}
//

$actionLinkCloseDownload = Url::to('@web/company/closedownload');
$tender_id = $model->id;
$tender_close = $model->tender_close;

$script = <<< JS

function sendCloseDownload() {
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'tender_id=' + '$tender_id' + '&tender_close=' + '$tender_close',
                url  : '$actionLinkCloseDownload',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                location.reload();
                
                } else {
                // Неудачно
                }
                
                }
                });
}

// Клик закрыть загрузку в тендере
$('.btn-danger').on('click', function(){
    var checkCloseDownload = confirm("Вы уверены что хотите закрыть загрузку?");
    
    if(checkCloseDownload == true) {     
       sendCloseDownload();
    }
});
$('.btn-warning').on('click', function(){
    var checkCloseDefault = confirm("Вы уверены что хотите открыть загрузку?");
  
    if(checkCloseDefault == true) {
      sendCloseDownload();
     } 
});

// открываем модальное окно добавить вложения
$('.showFormAttachButt').on('click', function(){
$('#showFormAttach').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');

JS;
$this->registerJs($script, View::POS_READY);
?>

<table class="table table-bordered list-data">
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('purchase_status') ?></td>
        <td>
            <?php

            $arrPurchstatus = isset($arrLists[0]) ? $arrLists[0] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'purchase_status',
                'displayValue' => $model->purchase_status ? $arrPurchstatus[$model->purchase_status] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrPurchstatus,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_status_proc') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_status_proc',
                'displayValue' => nl2br($model->comment_status_proc),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий к статусу закупки'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('user_id') ?></td>
        <td>
            <?php

            $usersList = isset($arrLists[1]) ? $arrLists[1] : [];

            $arrUserTend = explode(', ', $model->user_id);
            $userText = '';

            if (count($arrUserTend) > 1) {

                for ($i = 0; $i < count($arrUserTend); $i++) {
                    if(isset($usersList[$arrUserTend[$i]])) {
                        $userText .= $usersList[$arrUserTend[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($usersList[$model->user_id])) {
                        $userText = $usersList[$model->user_id];
                    }
                } catch (\Exception $e) {
                    $userText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'user_id',
                'displayValue' => $userText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $usersList,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_search') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_search',
                'displayValue' => date('d.m.Y', $model->date_search),
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => date('d.m.Y', $model->date_search)]
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_start',
                'displayValue' => $model->date_request_start ? date('d.m.Y', $model->date_request_start) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => $model->date_request_start ? date('d.m.Y', $model->date_request_start) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_end') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_end',
                'displayValue' => $model->date_request_end ? date('d.m.Y H:i', $model->date_request_end) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->date_request_end ? date('d.m.Y H:i', $model->date_request_end) : ''],
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
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_request_process') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_request_process',
                'displayValue' => $model->time_request_process ? date('d.m.Y H:i', $model->time_request_process) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_request_process ? date('d.m.Y H:i', $model->time_request_process) : ''],
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
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_bidding_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_bidding_start',
                'displayValue' => $model->time_bidding_start ? date('d.m.Y H:i', $model->time_bidding_start) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_bidding_start ? date('d.m.Y H:i', $model->time_bidding_start) : ''],
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
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_bidding_end') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_bidding_end',
                'displayValue' => $model->time_bidding_end ? date('d.m.Y H:i', $model->time_bidding_end) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_bidding_end ? date('d.m.Y H:i', $model->time_bidding_end): ''],
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
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'customer',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_customer',
                'displayValue' => nl2br($model->comment_customer),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий к полю "Заказчик"'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('inn_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'inn_customer',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('contacts_resp_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'contacts_resp_customer',
                'displayValue' => nl2br($model->contacts_resp_customer),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите контакты ответственных лиц заказчика'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('method_purchase') ?></td>
        <td>
            <?php

            $arrMethods = isset($arrLists[2]) ? $arrLists[2] : [];

            $arrMethodsTend = explode(', ', $model->method_purchase);
            $methodsText = '';

            if (count($arrMethodsTend) > 1) {

                for ($i = 0; $i < count($arrMethodsTend); $i++) {
                    if(isset($arrMethods[$arrMethodsTend[$i]])) {
                        $methodsText .= $arrMethods[$arrMethodsTend[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($arrMethods[$model->method_purchase])) {
                        $methodsText = $arrMethods[$model->method_purchase];
                    }
                } catch (\Exception $e) {
                    $methodsText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'method_purchase',
                'displayValue' => $methodsText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrMethods,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);  ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('city') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'city',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('service_type') ?></td>
        <td>
            <?php

            $ServicesList = isset($arrLists[3]) ? $arrLists[3] : [];

            $arrServices = explode(', ', $model->service_type);
            $serviceText = '';

            if (count($arrServices) > 1) {

                for ($i = 0; $i < count($arrServices); $i++) {
                    if(isset($ServicesList[$arrServices[$i]])) {
                        $serviceText .= $ServicesList[$arrServices[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($ServicesList[$model->service_type])) {
                        $serviceText = $ServicesList[$model->service_type];
                    }
                } catch (\Exception $e) {
                    $serviceText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'service_type',
                'displayValue' => $serviceText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $ServicesList,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('federal_law') ?></td>
        <td>
            <?php

            $arrFZlist = isset($arrLists[4]) ? $arrLists[4] : [];

            $arrFZ = explode(', ', $model->federal_law);
            $FZText = '';

            if (count($arrFZ) > 1) {

                for ($i = 0; $i < count($arrFZ); $i++) {
                    if(isset($arrFZlist[$arrFZ[$i]])) {
                        $FZText .= $arrFZlist[$arrFZ[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($arrFZlist[$model->federal_law])) {
                        $FZText = $arrFZlist[$model->federal_law];
                    }
                } catch (\Exception $e) {
                    $FZText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'federal_law',
                'displayValue' => $FZText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrFZlist,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('notice_eis') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'notice_eis',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('number_purchase') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'number_purchase',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('place') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'place',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('key_type') ?></td>
        <td>
            <?php

            $arrKeyTypeList = isset($arrLists[5]) ? $arrLists[5] : [];

            $arrKeyType = explode(', ', $model->key_type);
            $keyTypeText = '';

            if (count($arrKeyType) > 1) {

                for ($i = 0; $i < count($arrKeyType); $i++) {
                    if(isset($arrKeyTypeList[$arrKeyType[$i]])) {
                        $keyTypeText .= $arrKeyTypeList[$arrKeyType[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($arrKeyTypeList[$model->key_type])) {
                        $keyTypeText = $arrKeyTypeList[$model->key_type];
                    }
                } catch (\Exception $e) {
                    $keyTypeText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'key_type',
                'displayValue' => $keyTypeText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrKeyTypeList,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);  ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('price_nds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'price_nds',
                'displayValue' => $model->price_nds ? ($model->price_nds . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_purchase_price') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_purchase_price',
                'displayValue' => $model->maximum_purchase_price ? ($model->maximum_purchase_price . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('final_price') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'final_price',
                'displayValue' => $model->final_price ? ($model->final_price . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('cost_purchase_completion') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'cost_purchase_completion',
                'displayValue' => $model->cost_purchase_completion ? ($model->cost_purchase_completion . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('pre_income') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'pre_income',
                'displayValue' => $model->pre_income ? ($model->pre_income . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('percent_down') ?></td>
        <td>
            <?php
            // Вычисление значиния для вывода Процентное снижение по завершению закупки в процентах
            $resPerDown = '';

            if($model->percent_down === 0) {
                $resPerDown = 0 . '%';
            } else if($model->percent_down > 0) {
                $resPerDown = $model->percent_down . '%';
            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'percent_down',
                'displayValue' => $resPerDown,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100],
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_purchase_nds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_purchase_nds',
                'displayValue' => $model->maximum_purchase_nds ? ($model->maximum_purchase_nds . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_purchase_notnds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_purchase_notnds',
                'displayValue' => $model->maximum_purchase_notnds ? ($model->maximum_purchase_notnds . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('percent_max') ?></td>
        <td>
            <?php
            // Вычисление значиния для вывода Максимальное согласованное расчетное снижение в процентах
            $resPerMax = '';

            if($model->percent_max === 0) {
                $resPerMax = 0 . '%';
            } else if($model->percent_max > 0) {
                $resPerMax = $model->percent_max . '%';
            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'percent_max',
                'displayValue' => $resPerMax,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100],
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_agreed_calcnds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_agreed_calcnds',
                'displayValue' => $model->maximum_agreed_calcnds ? ($model->maximum_agreed_calcnds . ' ₽') : '',

                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_agreed_calcnotnds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_agreed_calcnotnds',
                'displayValue' => $model->maximum_agreed_calcnotnds ? ($model->maximum_agreed_calcnotnds . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('site_fee_participation') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'site_fee_participation',
                'displayValue' => $model->site_fee_participation ? ($model->site_fee_participation . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('ensuring_application') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'ensuring_application',
                'displayValue' => $model->ensuring_application ? ($model->ensuring_application . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('status_request_security') ?></td>
        <td>
            <?php

            $arrStatusRequestList = isset($arrLists[6]) ? $arrLists[6] : [];

            $arrStatusRequest = explode(', ', $model->status_request_security);
            $statusRequestText = '';

            if (count($arrStatusRequest) > 1) {

                for ($i = 0; $i < count($arrStatusRequest); $i++) {
                    if(isset($arrStatusRequestList[$arrStatusRequest[$i]])) {
                        $statusRequestText .= $arrStatusRequestList[$arrStatusRequest[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($arrStatusRequestList[$model->status_request_security])) {
                        $statusRequestText = $arrStatusRequestList[$model->status_request_security];
                    }
                } catch (\Exception $e) {
                    $statusRequestText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_request_security',
                'displayValue' => $statusRequestText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrStatusRequestList,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_status_request') ?></td>
        <td>
            <?= ($model->date_status_request) ? date('d.m.Y H:i', $model->date_status_request) : '-' ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('contract_security') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'contract_security',
                'displayValue' => $model->contract_security ? ($model->contract_security . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('status_contract_security') ?></td>
        <td>
            <?php

            $arrStatusContractList = isset($arrLists[7]) ? $arrLists[7] : [];

            $arrStatusContract = explode(', ', $model->status_contract_security);
            $statusContractText = '';

            if (count($arrStatusContract) > 1) {

                for ($i = 0; $i < count($arrStatusContract); $i++) {
                    if(isset($arrStatusContractList[$arrStatusContract[$i]])) {
                        $statusContractText .= $arrStatusContractList[$arrStatusContract[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($arrStatusContractList[$model->status_contract_security])) {
                        $statusContractText = $arrStatusContractList[$model->status_contract_security];
                    }
                } catch (\Exception $e) {
                    $statusContractText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_contract_security',
                'displayValue' => $statusContractText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrStatusContractList,
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_status_contract') ?></td>
        <td>
            <?= ($model->date_status_contract) ? date('d.m.Y H:i', $model->date_status_contract) : '-' ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('competitor') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'competitor',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('inn_competitors') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'inn_competitors',
                'displayValue' => nl2br($model->inn_competitors),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите ИНН конкурентов'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_contract',
                'displayValue' => ($model->date_contract) ? date('d.m.Y', $model->date_contract) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => ($model->date_contract) ? date('d.m.Y', $model->date_contract) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('term_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'term_contract',
                'displayValue' => ($model->term_contract) ? date('d.m.Y', $model->term_contract) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => ($model->term_contract) ? date('d.m.Y', $model->term_contract) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_date_contract',
                'displayValue' => nl2br($model->comment_date_contract),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>




    <tr>
        <td class="list-label-md">Осталось дней до окончания действия договора</td>
        <td>

            <?php

            if($model->term_contract) {
                $timeNow = time();

                $showTotal = '';

                if ($model->term_contract > $timeNow) {

                    $totalDate = $model->term_contract - $timeNow;

                    $days = ((Int)($totalDate / 86400));
                    $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                    if ($days < 0) {
                        $days = 0;
                    }

                    $showTotal .= $days . ' д.';

                } else {
                    $totalDate = $timeNow - $model->term_contract;

                    $days = ((Int)($totalDate / 86400));
                    $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                    if ($days < 0) {
                        $days = 0;
                    }
                    $showTotal .= '- ' . $days . ' д.';
                }

                echo $showTotal;
            }

            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment',
                'displayValue' => nl2br($model->comment),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('files') ?></td>
        <td>
            <?php

            $pathfolder = \Yii::getAlias('@webroot/files/tenders/' . $model->id . '/');
            $shortPath = '/files/tenders/' . $model->id . '/';

            if (file_exists($pathfolder)) {

                $numFiles = 0;
                $resLinksFiles = '';
                $arrStateID = [];

                foreach (\yii\helpers\FileHelper::findFiles($pathfolder) as $file) {

                    $resLinksFiles .= Html::a(basename($file), $shortPath . basename($file), ['target' => '_blank']) . '<br />';
                    $numFiles++;

                }

                if($numFiles > 0) {
                    echo $resLinksFiles;
                } else {
                    echo '-<br />';
                }

            } else {
                echo '-<br />';
            }

            ?>

            <?php
            if ($model->tender_close == 1) {

            } else {
                echo '<br /><span class="btn btn-primary btn-sm showFormAttachButt" style="margin-right:15px;">Добавить вложение</span>';
            }
            ?>
        </td>
    </tr>
   <?php
   if ($model->tender_close == 1) {
       echo "<tr> 
        <td class='list-label-md'>Закупка закрыта</td>
        <td><span style='color:#BA0006'>Закупка была закрыта, поэтому внести изменения невозможно</span> <span class='btn btn-warning' style='display:none'>Открыть закупку</span></td>
        </tr>";
   } else {
       echo "<tr>
        <td class='list-label-md'>Закрыть закупку</td>
        <td> <span class='btn btn-danger'>Закрыть закупку</span></td>
        </tr>";
   }
 ?>
</table>

<?php
// Модальное окно добавить вложения
$pathfolder = \Yii::getAlias('@webroot/files/tenders/' . $model->id . '/');
$shortPath = '/files/tenders/' . $model->id . '/';

$modalAttach = Modal::begin([
'header' => '<h5>Добавить вложения</h5>',
'id' => 'showFormAttach',
'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
'size'=>'modal-lg',
]);

echo "<div style='font-size: 15px; margin-left:15px;'>Выберите файлы:</div>";

$modelAddAttach = new \yii\base\DynamicModel(['files']);
$modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

$form = ActiveForm::begin([
'action' => ['/company/newtendattach', 'id' => $model->id],
'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
'fieldConfig' => [
'template' => '<div class="col-sm-6">{input}</div>',
'inputOptions' => ['class' => 'form-control input-sm'],
],
]);

echo $form->field($modelAddAttach, 'files[]')->fileInput(['multiple' => true]);

echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']);

ActiveForm::end();

Modal::end();
// Модальное окно добавить вложения
?>