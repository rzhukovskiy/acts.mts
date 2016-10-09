<?php
/**
 * @var $model \common\models\Company
 * @var $modelEntry \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
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
            'action' => ['entry/create'],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td colspan="6">
                    <label class="control-label">Адрес:</label> <?= $model->info->address ?>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <label class="control-label">Телефон:</label> <?= $model->info->phone ?>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <label class="control-label">Свободное время:</label>
                    <div class="free-time">
                        <?php
                        $step = 0;
                        $listEntry = $searchModel->search([])->getModels();
                        foreach ($listEntry as $entry) {
                            if (!$step) {
                                if (date('H:i', $entry->start_at) != '08:00') {
                                    echo '<div class="col-sm-3">08:00 - ' . date('H:i', $entry->start_at) . '</div><div class="col-sm-3">';
                                } else {
                                    echo '<div class="col-sm-3">';
                                }
                            } else {
                                echo date('H:i', $entry->start_at) . '</div><div class="col-sm-3">';
                            }
                            $step++;
                            if ($step == count($listEntry)) {
                                if (date('H:i', $entry->end_at) != '20:00') {
                                    echo date('H:i', $entry->end_at) . ' - 20:00</div>';
                                } else {
                                    echo '</div>';
                                }
                            } else {
                                echo date('H:i', $entry->end_at) . ' - ';
                            }
                        } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 150px">
                    <?= $form->field($modelEntry, 'start_str')->widget(TimePicker::classname(), [
                        'pluginOptions' => [
                            'defaultTime' => '8:00',
                            'showMeridian' => false,
                        ],
                        'options' => [
                            'class' => 'form-control',
                        ]
                    ])->error(false) ?>
                </td>
                <td style="width: 100px">
                    <?= $form->field($modelEntry, 'card_id')->textInput(); ?>
                </td>
                <td class="complex-number">
                    <label class="control-label" for="act-card_id">Номер <span class="extra-number"
                                                                               style="display:none">и номер прицепа</span></label>

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