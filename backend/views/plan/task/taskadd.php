<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\TaskUser;
use kartik\datetime\DateTimePicker;
use common\models\User;
use yii\web\View;

$this->title = 'Добавление';

$userListSearch = json_encode($userListsID);

if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 176)) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Добавление', 'url' => ['plan/taskadd'], 'active' => Yii::$app->controller->action->id == 'taskadd'],
    ];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Добавление', 'url' => ['plan/taskadd'], 'active' => Yii::$app->controller->action->id == 'taskadd'],
    ];
}
echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $tabs,
]);

$getID = Yii::$app->request->get('id');

$script = <<< JS

    // добавляем кнопку поиск
   $('.field-taskuser-for_user').html('<span class="btn btn-warning btn-sm searchButtom">Поиск</span>' + $('.field-taskuser-for_user').html()); 
   // добавляем скрытое поле поиска
   $('.field-taskuser-for_user div').html('<input id="searchText" style="display: none; margin-bottom: 20px;" type="text" class="form-control" name="searchText" placeholder="Поиск мойки">' + $('.field-taskuser-for_user div').html());
   // открываем скрытое поле поиска
   $('.searchButtom').on('click', function() {
    
       $('#searchText').show();  
       $(this).hide();

   });

    var arr3 = $userListSearch;
    var nowValue = '';   
    var thisvalue = '';   
    // при вводе в поле поиска скрываем в селекторе ненужные
    $('#searchText').keyup(function() {
         
         nowValue = $(this).val().toLowerCase();
         $('#taskuser-for_user').val('');
         
         $('#taskuser-for_user').find('option').each(function(){
           
             thisvalue = $(this).val();
             
             if ($(this).val() !== '') {
             $(this).contents().unwrap().wrap('<input value='+ thisvalue + '>');
             }
     });

           $.each(arr3,function(key,data) {
               
                  if ((data.toString().toLowerCase()).indexOf(nowValue) !== -1) {
                      $('#taskuser-for_user input[value="' + key + '"]').contents().unwrap().wrap('<option value='+ key + '>');
                  } 
                  
                });  
       
});
JS;
$this->registerJs($script, View::POS_READY);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['/plan/taskadd'],
            'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>
        <?= $form->field($model, 'for_user')->dropDownList($userListsID, ['class' => 'form-control', 'prompt' => 'Выберите пользователя']) ?>
        <?= $form->field($newmodellink, 'for_user_copy')->dropDownList($userListsID, ['class' => 'form-control', 'multiple' => 'true']) ?>
        <?= $form->field($model, 'priority')->dropDownList(TaskUser::$priorityStatus, ['class' => 'form-control']) ?>
        <?php if ($getID) {
            echo $form->field($model, 'title')->input('text', ['value' => 'Проработать цены по тендеру №' . $getID]);
        } else {
            echo $form->field($model, 'title')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите тему']);
        }
        ?>
        <?php if ($getID) {
            echo $form->field($model, 'task')->textarea(['maxlength' => true, 'rows' => '4', 'value' => 'Нужно проработать цены по тендеру']);
        } else {
            echo $form->field($model, 'task')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите задачу']);
        }
        ?>
        <?= $form->field($model, 'data')->widget(DateTimePicker::className(), [
            'type' => DateTimePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Выберите дату и время'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy hh:i',
                'autoclose'=>true,
                'weekStart'=>1,
                'todayBtn'=>true,
            ]
        ]) ?>
        <?php if ($getID) {
            echo $form->field($model, 'tender_id')->input('number', ['value' => $getID]);
        } else {
            echo $form->field($model, 'tender_id')->input('number', ['class' => 'form-control', 'placeholder' => 'Введите ID тендера']);
        }
         ?>

        <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>


        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>