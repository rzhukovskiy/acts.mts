<?php

/**
 * @var $searchModel \common\models\search\CompanySearch
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $listCity array
 * @var $type integer
 */

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$selCity = '';

if(isset(Yii::$app->request->queryParams['CompanySearch']['address'])) {

    if(Yii::$app->request->queryParams['CompanySearch']['address'] != '') {
        $selCity = Yii::$app->request->queryParams['CompanySearch']['address'];
    } else {
        $selCity = 'Архангельск';
    }

} else {
    $selCity = 'Архангельск';
}

$script = <<< JS

$('#companysearch-card_number').on('change', function () {
    
    if(($(this).val() != '') && ($(this).val().length > 0)) {
        $('#companysearch-address').val("");
    } else {
        $('#companysearch-address').val("$selCity");
    }
    
});

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Выбор города <?= $companyName ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'wash-form',
            'method' => 'get',
            'options' => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin: 20px 0;'],
            'fieldConfig' => [
                'template' => '{input}',
                'inputOptions' => ['class' => 'form-control input-sm'],
                'options' => ['class' => 'col-sm-3'],
            ],
        ]) ?>
        <?= $form->field($searchModel, 'card_number')->textInput(['placeholder' => 'номер карты']); ?>
        <?= $form->field($searchModel, 'address')->dropdownList($listCity, ['prompt' => 'выберите город']); ?>
        <?= $form->field($entrySearchModel, 'day')->widget(DatePicker::classname(), [
            'size' => 'lg',
            'removeButton' => false,
            'type' => DatePicker::TYPE_INPUT,
            'language' => 'ru',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy'
            ],
            'options' => [
                'class' => 'form-control input-sm datepicker',
                'readonly' =>'true',
                'value' => $entrySearchModel->day,
            ]
        ])->error(false); ?>
        <?= Html::submitButton('Показать', ['class' => 'btn btn-primary btn-sm']) ?>

        <?php ActiveForm::end() ?>
    </div>
</div>