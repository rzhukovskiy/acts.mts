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
$arrsite = [];
if (isset($arrLists[8])){
    $arrsite = $arrLists[8];
    asort($arrsite);
}
$arrtype = [];
if (isset($arrLists[9])){
    $arrtype = $arrLists[9];
    asort($arrtype);
}

$actionLink = Url::to('@web/company/controlisarchive');
$control_id = $model->id;
$is_archive = $model->is_archive;

$script = <<< JS

function sendControlisarchive() {
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'control_id=' + '$control_id' + '&is_archive=' + '$is_archive',
                url  : '$actionLink',
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
$('.closeTender').on('click', function(){
    var checkisarchive = confirm("Вы уверены что хотите закрыть?");
    
    if(checkisarchive == true) {     
       sendControlisarchive();
    }
});
$('.openTender').on('click', function(){
    var checkisarchiveDefault = confirm("Вы уверены что хотите открыть?");
  
    if(checkisarchiveDefault == true) {
      sendControlisarchive();
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
            <td class="list-label-md"><?= $model->getAttributeLabel('user_id') ?></td>
            <td>
                <?php

                echo Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'user_id',
                    'displayValue' => isset($usersList[$model->user_id]) ? $usersList[$model->user_id] : '',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'data' => $usersList,
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id]
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('send') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'send',
                    'displayValue' => $model->send ? ($model->send . ' ₽') : '',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN && $model->is_archive == 0) ? false : true,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('date_send') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'date_send',
                    'displayValue' => $model->date_send ? date('d.m.Y', $model->date_send) : '',
                    'inputType' => Editable::INPUT_DATE,
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => [
                        'class' => 'form-control',
                        'removeButton' => false,
                        'pluginOptions' => [
                            'format' => 'dd.mm.yyyy',
                            'autoclose' => true,
                            'pickerPosition' => 'bottom-right',
                        ],
                        'options'=>['value' => $model->date_send ? date('d.m.Y', $model->date_send) : '']
                    ],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]);
                ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('date_enlistment') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'date_enlistment',
                    'displayValue' => $model->date_enlistment ? date('d.m.Y', $model->date_enlistment) : '',
                    'inputType' => Editable::INPUT_DATE,
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => [
                        'class' => 'form-control',
                        'removeButton' => false,
                        'pluginOptions' => [
                            'format' => 'dd.mm.yyyy',
                            'autoclose' => true,
                            'pickerPosition' => 'bottom-right',
                        ],
                        'options'=>['value' => $model->date_enlistment ? date('d.m.Y', $model->date_enlistment) : '']
                    ],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]);
                ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('site_address') ?></td>
            <td>
                <?php


                echo Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'site_address',
                    'displayValue' => isset($arrsite[$model->site_address]) ? $arrsite[$model->site_address] : '',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'data' => $arrsite,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id]
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('platform') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'platform',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
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
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('purchase') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'purchase',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('eis_platform') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'eis_platform',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('type_payment') ?></td>
            <td>
                <?php

                echo Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'type_payment',
                    'displayValue' => isset($arrtype[$model->type_payment]) ? $arrtype[$model->type_payment] : '',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'data' => $arrtype,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id]
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('money_unblocking') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'money_unblocking',
                    'displayValue' => $model->money_unblocking ? date('d.m.Y', $model->money_unblocking) : '',
                    'inputType' => Editable::INPUT_DATE,
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => [
                        'class' => 'form-control',
                        'removeButton' => false,
                        'pluginOptions' => [
                            'format' => 'dd.mm.yyyy',
                            'autoclose' => true,
                            'pickerPosition' => 'bottom-right',
                        ],
                        'options'=>['value' => $model->money_unblocking ? date('d.m.Y', $model->money_unblocking) : '']
                    ],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]);
                ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('return') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'return',
                    'displayValue' => $model->return ? ($model->return . ' ₽') : '',
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN && $model->is_archive == 0) ? false : true,
                    'options' => ['class' => 'form-control'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md"><?= $model->getAttributeLabel('date_return') ?></td>
            <td>
                <?= Editable::widget([
                    'model' => $model,
                    'buttonsTemplate' => '{submit}',
                    'submitButton' => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute' => 'date_return',
                    'displayValue' => $model->date_return ? date('d.m.Y', $model->date_return) : '',
                    'inputType' => Editable::INPUT_DATE,
                    'asPopover' => true,
                    'placement' => PopoverX::ALIGN_LEFT,
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => [
                        'class' => 'form-control',
                        'removeButton' => false,
                        'pluginOptions' => [
                            'format' => 'dd.mm.yyyy',
                            'autoclose' => true,
                            'pickerPosition' => 'bottom-right',
                        ],
                        'options'=>['value' => $model->date_return ? date('d.m.Y', $model->date_return) : '']
                    ],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]);
                ?>
            </td>
        </tr>
        <tr>
            <td class="list-label-md">Остаток в работе</td>
            <td>
                <?php
                if($model->send || $model->return) {
                    echo ($model->send - $model->return) . ' ₽';
                } else {
                    echo "-";
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
                    'size' => 'lg',
                    'disabled' => $model->is_archive == 1 ? true : false,
                    'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                    'formOptions' => [
                        'action' => ['/company/updatecontroltender', 'id' => $model->id],
                    ],
                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                ]); ?>
            </td>
        </tr>
        <?php
        if ($model->is_archive == 1) {
            echo "<tr> 
        <td class='list-label-md'>Закрыто</td>
        <td><span style='color:#BA0006'>Вносить изменения невозможно</span> <span class='btn btn-warning openTender' style='display:none'>Открыть</span></td>
        </tr>";
        } else {
            echo "<tr>
        <td class='list-label-md'>Закрыть</td>
        <td> <span class='btn btn-danger closeTender'>Закрыть</span></td>
        </tr>";
        }
        ?>
    </table>
