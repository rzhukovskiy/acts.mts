<?php
/**
 * @var $model \common\models\Entry
 */

use common\models\Car;
use common\models\Mark;
use common\models\Type;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\widgets\ActiveForm;
use kartik\time\TimePicker;

$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['entry/create'] : ['entry/update', 'id' => $model->id],
    'id' => 'act-form',
]) ?>
<table class="table table-bordered">
    <tbody>
    <tr>
        <td style="width: 150px">
            <?= $form->field($model, 'start_str')->widget(TimePicker::classname(), [
                'pluginOptions' => [
                    'defaultTime' => $model->isNewRecord ? '8:00' : date('H:i', $model->start_at),
                    'showMeridian' => false,
                ],
                'options' => [
                    'class' => 'form-control',
                ]
            ])->error(false) ?>
        </td>
        <td style="width: 100px">
            <?= $form->field($model, 'card_number')->textInput(); ?>
        </td>
        <td class="complex-number">
            <label class="control-label" for="act-card_id">Номер <span class="extra-number"
                                                                       style="display:none">и номер прицепа</span></label>

            <div class="input-group" style="width: 100%;">
                <?= AutoComplete::widget([
                    'model' => $model,
                    'attribute' => 'number',
                    'options' => ['class' => 'form-control main-number', 'autocomplete' => 'on', 'style' => 'width: 50%'],
                    'clientOptions' => [
                        'source' => Car::find()->where(['!=', 'type_id', 7])->select('number as value')->asArray()->all(),
                        'minLength' => '2',
                        'autoFill' => true,
                    ]
                ]) ?>
                <?= AutoComplete::widget([
                    'model' => $model,
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
            <?= $form->field($model, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
        </td>
        <td style="width: 250px">
            <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(), ['max-width'])->error(false) ?>
        </td>
        <td style="width: 150px">
            <label class="control-label">Действие</label>
            <?= Html::submitButton($model->isNewRecord ? 'Записать' : 'Изменить', ['class' => 'btn btn-primary']) ?>
            <?= Html::activeHiddenInput($model, 'day') ?>
            <?= Html::activeHiddenInput($model, 'company_id') ?>
            <?= Html::activeHiddenInput($model, 'service_type') ?>
        </td>
    </tr>
    </tbody>
</table>
<?php ActiveForm::end() ?>