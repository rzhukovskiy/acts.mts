<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 */

use common\models\Car;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\web\View;

if (!empty($serviceList)) {
    $fixedList = json_encode(Service::find()
        ->andWhere(['type' => $model->service_type])
        ->select('is_fixed')->indexBy('id')->column());
    
    $script = <<< JS
    var serviceList = $fixedList;
    $('.scope-price').hide();

    $(document).on('change', '.scope-service', function () {
        var fixed = serviceList[$(this).val()];
        if (fixed > 0) {
            $(this).parent().parent().find('.scope-price').hide();
        } else {
            $(this).parent().parent().find('.scope-price').show();
        }
    });
JS;
    $this->registerJs($script, View::POS_READY);
}

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавить машину
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ?  [
                'act/create',
                'type' => $model->service_type,
            ] : [
                'act/update',
                'id' => $model->id
            ],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>
                    <?= $form->field($model, 'time_str')->widget(DatePicker::classname(), [
                        'type' => DatePicker::TYPE_INPUT,
                        'language' => 'ru',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'dd-mm-yyyy'
                        ],
                        'options' => [
                            'class' => 'form-control',
                            'value' => date('d-m-Y'),
                        ]
                    ])->error(false) ?>
                </td>
                <td style="min-width: 100px">
                    <?= $form->field($model, 'card_number')->textInput(); ?>
                </td>
                <td>
                    <?= $form->field($model, 'car_number')->widget(AutoComplete::classname(),
                    [
                        'options'       => ['class' => 'form-control', 'autocomplete' => 'on'],
                        'clientOptions' => [
                            'source'    => Car::find()->select('number as value')->asArray()->all(),
                            'minLength' => '2',
                            'autoFill'  => true,
                        ],
                        'clientEvents'  => [
                            'response' => 'function (event, ui) {
                                if(ui.content.length==0){
                                    $("#act-mark_id").show();
                                    $("#act-type_id").show();
                                }else{
                                    $("#act-mark_id").hide();
                                    $("#act-type_id").hide();
                                }
                            }'
                        ],
                    ])->error(false) ?>
                </td>
                <td style="width: 20%">
                    <?= $form->field($model, 'mark_id')
                        ->dropdownList(Mark::getMarkList(), ['style' => 'display:none'])
                        ->error(false) ?>
                </td>
                <td style="width: 20%">
                    <?= $form->field($model, 'type_id')
                        ->dropdownList(Type::getTypeList(), ['max-width', 'style' => 'display:none'])
                        ->error(false) ?>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <div class="form-group" style="height: 5px;">
                        <div class="col-xs-6">
                            <label class="control-label">Услуга</label>
                        </div>
                        <div class="col-xs-1">
                            <label class="control-label">Количество</label>
                        </div>
                        <div class="col-xs-1">
                            <label class="control-label">Цена</label>
                        </div>
                        <div class="col-xs-1">
                        </div>
                    </div>

                    <div class="form-group" style="height: 25px;">
                        <div class="col-xs-6">
                            <?php if (!empty($serviceList)) { ?>
                                <?= Html::dropDownList("Act[serviceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm scope-service', 'prompt' => 'выберите услугу']) ?>
                            <?php } else { ?>
                                <?= Html::textInput("Act[serviceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                            <?php } ?>
                        </div>
                        <div class="col-xs-1">
                            <?= Html::input('number', "Act[serviceList][0][amount]", 1, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                        </div>
                        <div class="col-xs-1">
                            <?= Html::input('text', "Act[serviceList][0][price]", 0, ['class' => 'form-control input-sm scope-price', 'placeholder' => 'Цена']) ?>
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-primary input-sm addButton"><i
                                    class="glyphicon glyphicon-plus"></i></button>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="7">
                    <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                    <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>