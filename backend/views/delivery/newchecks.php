<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \kartik\date\DatePicker;
use common\models\Company;
use yii\web\View;

$this->title = 'Добавление';

$companyList = json_encode($companyWash);

$script = <<< JS
scopeIndex = 0;
$('#w1').on('click', '.addButton', function(e)
    {
        scopeIndex++;
        e.preventDefault();

        var currentEntry = $(this).parents('.form-group:last'),
            newEntry = $(currentEntry.clone()).insertAfter(currentEntry);
        newEntry.find('input').each(function() {
            $(this).attr('name', $(this).attr('name').replace(/[0-9]+/g, scopeIndex));
        });

        newEntry.find('input').val('');
        currentEntry.find('.glyphicon-plus').removeClass('glyphicon-plus').addClass('glyphicon-minus');
        currentEntry.find('.addButton').removeClass('addButton').addClass('removeButton');
    }).on('click', '.removeButton', function(e)
    {
        $(this).parents('.form-group:first').remove();

        e.preventDefault();
        return false;
    });
    // добавляем кнопку поиск
   $('.field-historychecks-company_id').html('<span class="btn btn-warning btn-sm searchButtom">Поиск</span>' + $('.field-historychecks-company_id').html()); 
   // добавляем скрытое поле поиска
   $('.field-historychecks-company_id div').html('<input id="searchText" style="display: none; margin-bottom: 20px;" type="text" class="form-control" name="searchText" placeholder="Поиск мойки">' + $('.field-historychecks-company_id div').html());
   // открываем скрытое поле поиска
   $('.searchButtom').on('click', function() {
    
       $('#searchText').show();  
       $(this).hide();

   });

    var arr3 = $companyList;
    var nowValue = '';   
    var thisvalue = '';   
    // при вводе в поле поиска скрываем в селекторе ненужные
    $('#searchText').keyup(function() {
         
         nowValue = $(this).val().toLowerCase();
         $('#historychecks-company_id').val('');
         
         $('#historychecks-company_id').find('option').each(function(){
           
             thisvalue = $(this).val();
             
             if ($(this).val() !== '') {
             $(this).contents().unwrap().wrap('<input value='+ thisvalue + '>');
             }
     });

           $.each(arr3,function(key,data) {
               
                  if ((data.toString().toLowerCase()).indexOf(nowValue) !== -1) {
                      $('#historychecks-company_id input[value="' + key + '"]').contents().unwrap().wrap('<option value='+ key + '>');
                  } 
                  
                });  
       
});
JS;
$this->registerJs($script, View::POS_READY);
echo Tabs::widget([
    'items' => [
        ['label' => 'Список', 'url' => ['delivery/listchecks']],
        ['label' => 'Добавление', 'url' => ['delivery/newchecks'], 'active' => Yii::$app->controller->action->id == 'newchecks'],
    ],
]);


?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['/delivery/newchecks'] : ['/delivery/updatechecks'],
            'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'company_id')->dropDownList($companyWash, ['class' => 'form-control', 'prompt' => 'Выберите мойку']) ?>

        <div class="form-group" style="height: 25px;padding-bottom: 45px;">
                    <label class="col-sm-3 control-label" for="historychecks-serial_number">Серийные номера чеков</label>
                        <div class="col-sm-6">
                        <?= Html::input('text', "HistoryChecks[serial_number][0]", '', ['class' => 'form-control', 'placeholder' => 'Например: 900-999']) ?>
                        </div>

                            <button type="button" class="btn btn-primary input-sm addButton"><i
                                        class="glyphicon glyphicon-plus"></i></button>

        </div>
        <?= $form->field($model, 'date_send')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose' => true,
                'weekStart' => 1,
            ]
        ]) ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>