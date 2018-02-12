<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\bootstrap\Tabs;
use common\models\User;

$this->title = 'Редактирование';

echo Tabs::widget([
    'items' => [
        ['label' => 'Список', 'url' => ['delivery/listchemistry']],
        ['label' => 'Редактирование', 'url' => ['delivery/fullchemistry'], 'active' => Yii::$app->controller->action->id == 'fullchemistry'],
    ],
]);
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование' ?>
    </div>
    <div class="panel-body">
        <table class="table table-bordered list-data">
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('wash_name') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'wash_name',
                        'displayValue' => isset($model->wash_name) ? $model->wash_name : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите название мойки'],
                        'formOptions' => [
                            'action' => ['/delivery/updatechemistry', 'id' => $model->id],
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
                            'action' => ['/delivery/updatechemistry', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('size') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'size',
                        'displayValue' => isset($model->size) ? $model->size : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите литраж'],
                        'formOptions' => [
                            'action' => ['/delivery/updatechemistry', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
        </table>
    </div>
</div>
