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
        ['label' => 'Новые', 'url' => ['company/tenderownerlist?win=1']],
        ['label' => 'В работе', 'url' => ['company/tenderownerlist?win=0']],
        ['label' => 'Редактирование', 'url' => ['company/tenderownerfull', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'tenderownerfull'],
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
                <td class="list-label-md"><?= $model->getAttributeLabel('tender_user') ?></td>
                <td>
                    <?php

                    echo Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'tender_user',
                        'displayValue' => isset($usersList[$model->tender_user]) ? $usersList[$model->tender_user] : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'data' => $usersList,
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id]
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $model->getAttributeLabel('data') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'data',
                        'displayValue' => $model->data ? date('d.m.Y', $model->data) : '',
                        'inputType' => Editable::INPUT_DATE,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy',
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                            'options'=>['value' => $model->data ? date('d.m.Y', $model->data) : '']
                        ],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('text') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'text',
                        'displayValue' => nl2br($model->text),
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите текст'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('link') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'link',
                        'displayValue' => $model->link ? $model->link : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите ссылку'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('tender_id') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'tender_id',
                        'displayValue' => $model->tender_id,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите номер тендера'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
        </table>
    </div>
</div>
