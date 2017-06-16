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

echo $this->render('_modal', [
    'modelCompany' => $modelCompany,
]);

$workTime = $modelCompany->getWorkTimeArray();

$script = <<< JS
    $('#company-worktime-targ').click(function() {
        $('#everyday').hide();
        $('#anyday').show();
        $('.modaltime').appendTo('form#w21');
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
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $modelCompany->name ?>
        <div class="header-btn pull-right">
            <?= $modelCompany->status != Company::STATUS_ARCHIVE ?
                Html::a('В архив', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ARCHIVE], ['class' => 'btn btn-success btn-sm']) : '' ?>
            <?= $modelCompany->status != Company::STATUS_REFUSE ? 
                Html::a('В архив 2', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_REFUSE], ['class' => 'btn btn-success btn-sm']) : '' ?>
            <?= $modelCompany->status != Company::STATUS_ARCHIVE3 ?
                Html::a('В архив 3', ['company/status', 'id' => $modelCompany->id, 'status' => Company::STATUS_ARCHIVE3], ['class' => 'btn btn-success btn-sm']) : '' ?>

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

                            <?= Html::a('Проверить почтовое отправление',
                                'https://www.pochta.ru/tracking#' . $modelCompanyOffer->mail_number,
                                ['target' => 'blank', 'class' => 'btn btn-primary']) ?>

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
            }
            ?>

        </table>
    </div>
</div>