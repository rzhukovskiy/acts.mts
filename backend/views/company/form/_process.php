<?php

/**
 * @var $this yii\web\View
 * @var $modelCompany common\models\Company
 * @var $modelCompanyInfo common\models\CompanyInfo
 * @var $modelCompanyOffer common\models\CompanyOffer
 */
use kartik\editable\Editable;
use yii\helpers\Html;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $modelCompany->name ?>
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
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите название'],
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">Адрес</td>
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
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите город'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
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
                <td class="list-label-md"><?= $modelCompanyInfo->getAttributeLabel('phone') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'phone',
                        'asPopover' => true,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите телефон'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">Часы работы</td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'start_str',
                        'displayValue' => gmdate('H:i', $modelCompanyInfo->start_at),
                        'asPopover' => true,
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                        ],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                    ]); ?>
                    -
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'end_str',
                        'displayValue' => gmdate('H:i', $modelCompanyInfo->end_at),
                        'asPopover' => true,
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                        ],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
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
                        'submitOnEnter' => false,
                        'attribute' => 'process',
                        'displayValue' => $modelCompanyOffer->processHtml,
                        'inputType' => Editable::INPUT_TEXTAREA,
                        'asPopover' => true,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарии'],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
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
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите номер почтового отделения'],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
                    ]); ?>
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
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy hh:ii',
                                'autoclose'=>true,
                            ],
                        ],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
                    ]); ?>
                </td>
            </tr>
        </table>
    </div>
</div>