<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\helpers\Html;

$script = <<< JS

    if (($('.typeDay').val() == 0) || ($('.typeDay').val() == 1)) {
        $('.field-companyinfo-prepaid').hide();
    }
    
    if ($('.typeDay').val() == 4) {
        $('.field-companyinfo-payday').hide();
    }

$('.typeDay').on('change', function (e) {
    var valueSelected = this.value;
    
    if ((valueSelected == 0) || (valueSelected == 1)) {
        $('.field-companyinfo-prepaid').hide();
        $('.field-companyinfo-payday').show();
    } else if (valueSelected == 4) {
        $('.field-companyinfo-prepaid').show();
        $('.field-companyinfo-payday').hide();
    } else {
        $('.field-companyinfo-prepaid').show();
        $('.field-companyinfo-payday').show();
    }
    
});
    
JS;
$this->registerJs($script);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $modelCompanyInfo->company->name ?> :: Инфо
    </div>
    <div class="panel-body">
        <table class="table table-bordered list-data">
            <tr>
                <td class="list-label-md"><?= $modelCompanyInfo->getAttributeLabel('email') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'email',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите email'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelCompanyInfo->getAttributeLabel('address_mail') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'address_mail',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите адрес'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelCompanyInfo->getAttributeLabel('pay') ?></td>
                <td>
                    <?php $editableForm = Editable::begin([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'pay',
                        'displayValue' => $modelCompanyInfo->payData,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'дни оплаты', 'style' => 'display:none;'],
                        'formOptions' => [
                            'action' => ['/company-info/updatepay', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);

                    $form = $editableForm->getForm();
                    echo Html::hiddenInput('kv-complex', '1');

                    $arrPayData = explode(':', $modelCompanyInfo->pay);

                    $selpayTypeDay = '';
                    $selpayDay = '';
                    $selprePaid = '';

                    if(count($arrPayData) > 1) {
                        $selpayTypeDay = $arrPayData[0];
                        $selpayDay = $arrPayData[1];

                        if (count($arrPayData) == 3) {
                            $selprePaid = $arrPayData[2];
                        }

                    }

                    $editableForm->afterInput =
                        $form->field($modelCompanyInfo, 'payTypeDay')->dropDownList([0 => 'Банковские дни', 1 => 'Календарные дни', 2 => 'Аванс + банковские дни', 3 => 'Аванс + календарные дни', 4 => 'Аванс'], ['class' => 'form-control typeDay', 'options'=>[$selpayTypeDay => ['Selected'=>true]]]) .
                        $form->field($modelCompanyInfo, 'payDay')->dropDownList([3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45], ['class' => 'form-control dayPay', 'options'=>[$selpayDay => ['Selected'=>true]]]) .
                        $form->field($modelCompanyInfo, 'prePaid')->textInput(['class' => 'form-control prePaid', 'value' => $selprePaid, 'type' => 'number', 'placeholder' => 'Сумма аванса']);
                    Editable::end();

                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelCompanyInfo->getAttributeLabel('contract') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'contract',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Номер договора'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $modelCompanyInfo->getAttributeLabel('contract_date_str') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'contract_date_str',
                        'displayValue' => $modelCompanyInfo->contract_date_str,
                        'inputType' => Editable::INPUT_DATE,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy',
                                'autoclose'=>true,
                            ],
                        ],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
        </table>
    </div>
</div>