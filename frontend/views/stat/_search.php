<?php
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;

/**
 * @var $this \yii\web\View
 * @var $model \frontend\models\search\ActSearch
 * @var $type integer
 */

if (!is_null($type)) {
    $action = ['/statistic/list', 'type' => $type];
    if ($type == 'total')
        $action = ['/statistic/' . $type];
}
if (!empty($companyId))
    $action = ['/statistic/view', 'id' => $companyId];
?>
<div class="panel panel-primary">
    <div class="panel-heading">Выбор переода</div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $action,
            'method' => 'get',
            'id' => 'search-form',
            'options' => ['class' => 'form-inline col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-12">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]);
        ?>
        <?php
        echo $form->field($model, 'dateFrom')
            ->widget(DatePicker::className(), [
                'model' => $model,
                'attribute' => 'dateFrom',
                'attribute2' => 'dateTo',
                'type' => DatePicker::TYPE_RANGE,
                'separator' => '-',
                'pluginOptions' => ['format' => 'yyyy-mm-dd',],
                'options' => ['class' => 'input-sm'],
                'options2' => ['class' => 'input-sm'],
            ])->label(false);
        ?>
        <div class="form-group" style="padding-bottom: 11px;">
            <?= Html::submitButton('Показать', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>