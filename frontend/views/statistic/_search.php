<?php
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;

/**
 * @var $this \yii\web\View
 * @var $model \frontend\models\search\ActSearch
 * @var $type integer
 */

$form = ActiveForm::begin([
    'action' => ['/statistic/list', 'type' => $type],
    'method' => 'get',
    'id' => 'search-form',
    'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-2 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]);

echo $form->field($model, 'dateFrom')->widget(DatePicker::class, [
    'model' => $model,
    'attribute' => 'dateFrom',
    'attribute2' => 'dateTo',
    'type' => DatePicker::TYPE_RANGE,
    'separator' => '-',
    'pluginOptions' => ['format' => 'yyyy-mm-dd']
]);

?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-6">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
<?php

ActiveForm::end();