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
        ['label' => 'Архив', 'url' => ['company/tenderownerlist?win=2']],
        ['label' => 'Не взяли', 'url' => ['company/tenderownerlist?win=3']],
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
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
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
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('customer') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'customer',
                        'displayValue' => isset($model->customer) ? $model->customer : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите заказчика'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('customer_full') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'customer_full',
                        'displayValue' => isset($model->customer_full) ? $model->customer_full : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите полное наименование заказчика'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('purchase_name') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'purchase_name',
                        'displayValue' => isset($model->purchase_name) ? $model->purchase_name : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите что закупают'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('purchase') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'purchase',
                        'displayValue' => isset($model->purchase) ? $model->purchase . ' ₽' : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите сумму закупки'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('request_security') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'request_security',
                        'displayValue' => isset($model->request_security) ? $model->request_security . ' ₽' : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите обеспечение заявки'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('city') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'city',
                        'displayValue' => isset($model->city) ? $model->city : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите город'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('inn_customer') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'inn_customer',
                        'displayValue' => isset($model->inn_customer) ? $model->inn_customer : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите ИНН заказчика'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('fz') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'fz',
                        'displayValue' => isset($model->fz) ? $model->fz : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите тип заявки'],
                        'formOptions' => [
                            'action' => ['/company/tenderownerupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $model->getAttributeLabel('date_from') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'date_from',
                        'displayValue' => $model->date_from ? date('d.m.Y H:i', $model->date_from) : '',
                        'inputType' => Editable::INPUT_DATE,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy hh:ii',
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                            'options'=>['value' => $model->date_from ? date('d.m.Y H:i', $model->date_from) : '']
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
                <td class="list-label-md"><?= $model->getAttributeLabel('date_to') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'date_to',
                        'displayValue' => $model->date_to ? date('d.m.Y H:i', $model->date_to) : '',
                        'inputType' => Editable::INPUT_DATE,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy hh:ii',
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                            'options'=>['value' => $model->date_to ? date('d.m.Y H:i', $model->date_to) : '']
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
                <td class="list-label-md"><?= $model->getAttributeLabel('date_bidding') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'date_bidding',
                        'displayValue' => $model->date_bidding ? date('d.m.Y H:i', $model->date_bidding) : '',
                        'inputType' => Editable::INPUT_DATE,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy hh:ii',
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                            'options'=>['value' => $model->date_bidding ? date('d.m.Y H:i', $model->date_bidding) : '']
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
                <td class="list-label-md"><?= $model->getAttributeLabel('date_consideration') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'date_consideration',
                        'displayValue' => $model->date_consideration ? date('d.m.Y H:i', $model->date_consideration) : '',
                        'inputType' => Editable::INPUT_DATE,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy hh:ii',
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                            'options'=>['value' => $model->date_consideration ? date('d.m.Y H:i', $model->date_consideration) : '']
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
                    <?= $model->getAttributeLabel('link_official') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'link_official',
                        'displayValue' => $model->link_official ? $model->link_official : '',
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
                    <?= $model->getAttributeLabel('electronic_platform') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'electronic_platform',
                        'displayValue' => $model->electronic_platform ? $model->electronic_platform : '',
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
                    <?= $model->getAttributeLabel('link') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
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
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'tender_id',
                        'displayValue' => isset($model->tender_id) ? $model->tender_id : '',
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
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('reason_not_take') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'reason_not_take',
                        'displayValue' => isset($model->reason_not_take) ? nl2br($model->reason_not_take) : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите причину по которой не берете тендер'],
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
