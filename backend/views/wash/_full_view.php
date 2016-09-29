<?php
/**
 * @var $model \common\models\Company
 * @var $modelEntry \common\models\Entry
 */
use common\models\Car;
use common\models\Card;
use common\models\Mark;
use common\models\Type;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use kartik\time\TimePicker;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\jui\AutoComplete;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Запись на мойку <?= $model->name ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'wash-form',
            'method' => 'get',
            'options' => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-8">{input}</div>',
                'labelOptions' => ['class' => 'col-sm-4 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'address')->textInput(['readonly' => true]); ?>
        <?= $form->field($model, 'director')->textInput(['readonly' => true]); ?>
        <?= $form->field($model, 'phone')->textInput(['readonly' => true]); ?>
        <?php ActiveForm::end() ?>

        <?php
        $form = ActiveForm::begin([
            'action' => ['entry/create'],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td style="width: 150px">
                    <?= $form->field($modelEntry, 'start_str')->widget(TimePicker::classname(), [
                        'pluginOptions' => [
                            'minuteStep' => '30',
                            'defaultTime' => '8:00',
                            'showMeridian' => false,
                        ],
                        'options' => [
                            'class' => 'form-control',
                        ]
                    ])->error(false) ?>
                </td>
                <td style="width: 100px">
                    <?= $form->field($modelEntry, 'card_id')->widget(Select2::classname(), [
                        'data' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
                        'options' => ['class' => 'form-control', 'style' => 'min-width: 60px', 'placeholder' => ''],
                        'language' => 'ru',
                        'pluginOptions' => [
                            'initValueText' => 'asdasd',
                        ],
                    ])->error(false) ?>
                </td>
                <td class="complex-number">
                    <label class="control-label" for="act-card_id">Номер <span class="extra-number" style="display:none">и номер прицепа</span></label>
                    <div class="input-group" style="width: 100%;">
                        <?= AutoComplete::widget([
                            'model' => $modelEntry,
                            'attribute' => 'number',
                            'options' => ['class' => 'form-control main-number', 'autocomplete' => 'on', 'style' => 'width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['!=', 'type_id', 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ]
                        ]) ?>
                        <?= AutoComplete::widget([
                            'model' => $modelEntry,
                            'attribute' => 'extra_number',
                            'options' => ['class' => 'form-control input-group-addon extra-number', 'autocomplete' => 'on', 'style' => 'display:none; width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['type_id' => 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ]
                        ]) ?>
                    </div>
                </td>
                <td>
                    <?= $form->field($modelEntry, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
                </td>
                <td style="width: 250px">
                    <?= $form->field($modelEntry, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(), ['max-width'])->error(false) ?>
                </td>
                <td style="width: 150px">
                    <label class="control-label">Действие</label>
                    <?= Html::submitButton('Записать', ['class' => 'btn btn-primary']) ?>
                    <?= Html::activeHiddenInput($modelEntry, 'day') ?>
                    <?= Html::activeHiddenInput($modelEntry, 'company_id') ?>
                    <?= Html::activeHiddenInput($modelEntry, 'service_type') ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>