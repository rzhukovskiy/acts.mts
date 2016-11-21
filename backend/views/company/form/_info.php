<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;

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
                    <?= Editable::widget([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'pay',
                        'asPopover' => true,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'дни оплаты'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
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