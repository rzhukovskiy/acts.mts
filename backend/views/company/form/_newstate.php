<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use kartik\editable\Editable;
use kartik\popover\PopoverX;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $form yii\widgets\ActiveForm
 */
$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newstate', 'id' => $id] : ['/company/updatestate', 'id' => $model->id],
    'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'date')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату общения', 'value' => date('d.m.Y H:i')],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>

<?= $form->field($model, 'member_id')->dropDownList($companyMembers, ['class' => 'form-control', 'multiple' => 'true', 'size' => '4'/*, 'prompt' => 'Выберите сотрудника'*/]) ?>

<?= $form->field($model, 'author_id')->dropDownList($authorMembers, ['class' => 'form-control', 'options' =>[Yii::$app->user->identity->id => ['Selected' => true]], 'prompt' => 'Выберите сотрудника']) ?>

<?= $form->field($model, 'type')->dropDownList(['0' => 'Исходящий звонок' , '1' => 'Входящий звонок', '2' => 'Исходящее письмо', '3' => 'Входящее письмо'], ['class' => 'form-control'/*, 'prompt' => 'Выберите формат'*/]) ?>

<?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий']) ?>

<?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>

<div class="form-horizontal col-sm-10">
<div class="form-group">
    <label class="col-sm-3 control-label" for="companystate-files">
        <?= $modelCompanyOffer->getAttributeLabel('communication_str') ?>
    </label><div class="col-sm-6" style="margin-top: 7px;">
        <?php

        $wekCommunicDate = '';

        if(isset($modelCompanyOffer->communication_str)) {

            if (mb_strlen($modelCompanyOffer->communication_str) > 1) {

                try {
                    $CommunicDate = strtotime($modelCompanyOffer->communication_str);
                    $wekCommunicDate = date("w", $CommunicDate);

                    switch ($wekCommunicDate) {
                        case 1:
                            $wekCommunicDate = 'Понедельник';
                            break;
                        case 2:
                            $wekCommunicDate = 'Вторник';
                            break;
                        case 3:
                            $wekCommunicDate = 'Среда';
                            break;
                        case 4:
                            $wekCommunicDate = 'Четверг';
                            break;
                        case 5:
                            $wekCommunicDate = 'Пятница';
                            break;
                        case 6:
                            $wekCommunicDate = 'Суббота';
                            break;
                        case 7:
                            $wekCommunicDate = 'Воскресение';
                            break;
                    }

                    $wekCommunicDate = $modelCompanyOffer->communication_str . ' (' . $wekCommunicDate . ')';
                } catch (\Exception $e) {
                    $wekCommunicDate = $modelCompanyOffer->communication_str;
                }

            } else {
                $wekCommunicDate = $modelCompanyOffer->communication_str;
            }

        } else {
            $wekCommunicDate = $modelCompanyOffer->communication_str;
        }

        echo Editable::widget([
            'model' => $modelCompanyOffer,
            'buttonsTemplate' => '{submit}',
            'submitButton' => [
                'icon' => '<i class="glyphicon glyphicon-ok"></i>',
            ],
            'attribute' => 'communication_str',
            'displayValue' => $wekCommunicDate,
            'inputType' => Editable::INPUT_DATETIME,
            'asPopover' => true,
            'placement' => PopoverX::ALIGN_RIGHT,
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
    </div>
</div>
</div>

<div class="form-horizontal col-sm-10">
<div class="form-group">
    <label class="col-sm-3 control-label" for="companystate-files">
        <?= $modelCompanyOffer->getAttributeLabel('process') ?>
    </label><div class="col-sm-6" style="margin-top: 7px;">
        <?= Editable::widget([
            'model' => $modelCompanyOffer,
            'buttonsTemplate' => '{submit}',
            'submitButton' => [
                'icon' => '<i class="glyphicon glyphicon-ok"></i>',
            ],
            'placement' => PopoverX::ALIGN_RIGHT,
            'submitOnEnter' => false,
            'attribute' => 'process',
            'displayValue' => $modelCompanyOffer->processHtml,
            'inputType' => Editable::INPUT_TEXTAREA,
            'asPopover' => true,
            'size' => 'lg',
            'editableValueOptions' => ['style' => 'text-align: left'],
            'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий', 'style' => 'text-align: left', 'rows' => 7],
            'formOptions' => [
                'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
            ],
            'valueIfNull' => '<span class="text-danger">не задано</span>',
        ]); ?>
    </div>
</div>
</div>
