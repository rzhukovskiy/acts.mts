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
//use kartik\time\TimePicker;
use yii\helpers\Html;
include('_modal.php');
//echo $modelCompany->workTimeHtml;
?>

<?
$script = <<< JS
    $('#company-worktime-targ').click(function(){
        var value1 = $(this).text();
        $('.modaltime').appendTo('form#w20');
        $('.modaltime').show();     
    });
    
    $("#company-worktime-popover").find(".button").click(function () {
        alert("hi there");
        return false;
    });
    $('#graphwork').parent().click(function(){
        var value = $('[name="optradio"]:checked').closest('label').text();
        if(value == 'Круглосуточно'){
            var inputte = '00:00-00:00';
            $('input#company-worktime').val(inputte + '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte);
        }else if(value=='Каждый день'){
            var inputte = $("#w1").val() + '-' + $("#w2").val();
            $('input#company-worktime').val(inputte + '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte+ '\\n' + inputte);
        }else if(value=='Другой'){
            var monfrom = $("#w3").val();
            var monto = $("#w4").val();
            var tufrom = $("#w5").val();
            var tuto = $("#w6").val();
            var wedfrom = $("#w7").val();
            var wedto = $("#w8").val();
            var thufrom = $("#w9").val();
            var thuto = $("#w10").val();
            var frifrom = $("#w11").val();
            var frito = $("#w12").val();
            var sutfrom = $("#w13").val();
            var sutto = $("#w14").val();
            var sanfrom = $("#w15").val();
            var santo = $("#w16").val();
            $('input#company-worktime').val(
                monfrom + '-' + monto+ '\\n' + 
                tufrom + '-'  + tuto+ '\\n' + 
                wedfrom + '-' + wedto +'\\n' + 
                thufrom + '-' + thuto +'\\n' + 
                frifrom + '-' + frito +'\\n' + 
                sutfrom + '-' + sutto +'\\n' + 
                sanfrom + '-' + santo);
        }
    });
    $('[name="optradio"]').on('change', function () {
        var value = $('[name="optradio"]:checked').closest('label').text();
        if(value=='Круглосуточно'){
            $('#everyday').hide();
            $('#anyday').hide();

        }else if(value=='Каждый день'){
            $('#anyday').hide();
            $('#everyday').show();
        }else if(value=='Другой'){
            $('#everyday').hide();
            $('#anyday').show();
        }
    });
    
JS;
$this->registerJs($script, \yii\web\View::POS_READY);
?>


<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $modelCompany->name ?>
        <div class="header-btn pull-right">
            <?= $modelCompany->status != Company::STATUS_ARCHIVE ?
                Html::a('В архив', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ARCHIVE], ['class' => 'btn btn-success btn-sm']) : '' ?>
            <?= $modelCompany->status != Company::STATUS_REFUSE ? 
                Html::a('В архив 2', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_REFUSE], ['class' => 'btn btn-success btn-sm']) : '' ?>
            <?= $modelCompany->status != Company::STATUS_ACTIVE ?
                Html::a('В активные', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ACTIVE], ['class' => 'btn btn-success btn-sm']) : '' ?>
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
                <td class="list-label-md"><?= $modelCompanyOffer->getAttributeLabel('process') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyOffer,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'placement' => PopoverX::ALIGN_LEFT,
                        'submitOnEnter' => false,
                        'attribute' => 'process',
                        'displayValue' => $modelCompanyOffer->processHtml,
                        'inputType' => Editable::INPUT_TEXTAREA,
                        'asPopover' => true,
                        'size' => 'lg',
                        'editableValueOptions' => ['style' => 'text-align: left'],
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарии', 'style' => 'text-align: left', 'rows' => 10],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
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

                            <?= Html::a('Проверить почтовое отправление',
                                'https://www.pochta.ru/tracking#' . $modelCompanyOffer->mail_number,
                                ['target' => 'blank', 'class' => 'btn btn-primary']) ?>

                    </div>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelCompanyOffer->getAttributeLabel('communication_str') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyOffer,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'communication_str',
                        'displayValue' => $modelCompanyOffer->communication_str,
                        'inputType' => Editable::INPUT_DATETIME,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy hh:ii',
                                'autoclose' => true,
                                'pickerPosition' => 'top-right',
                            ],
                        ],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
        </table>
    </div>
</div>