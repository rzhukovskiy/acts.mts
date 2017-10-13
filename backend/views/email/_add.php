<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\FileHelper;

$actionLinkEmail = Url::to('@web/email/test');
$actionLinkDelete = Url::to('@web/email/deletefile');
$idEmail = $model->id;

$css = ".deleteFile {
color:#b21515;
font-size:13px;
text-decoration:underline;
}
.deleteFile:hover {
color:#d60c0c;
text-decoration:none;
cursor:pointer;
}
.noSaveFile {
color:#757575;
font-size:13px;
text-decoration:none;
}
.noSaveFile:hover {
color:#757575;
text-decoration:none;
cursor:default;
}";
$this->registerCss($css);

$script = <<< JS

var textTitle, textMail = '';

// функция проверки email
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

// открываем модальное окно предварительного просмотра
$('.btn-warning').on('click', function(){

    if($('input[name="Email[title]"]')) {
        textTitle = $('input[name="Email[title]"]').val();
        $('.previewEmailTitle').text(textTitle);
        $('.previewEmailTitle').css("font-weight", "bold")
    }

    if($('input[name="Email[text]"]')) {
        
        textMail = $('textarea[name="Email[text]"]').val();
        
        // Добавляем отступы как в форме
        var arrText = textMail.split('\\n');
        
        for(var i = 0; i < (arrText.length - 1); i++) {
        textMail = textMail.replace('\\n', "<br />");
        }
        // Добавляем отступы как в форме
        
        $('.previewEmailText').html(textMail);
    }
    
    $('#showModalPreview').modal('show');
});

    // Вложения
    
    var FormPreviewNoLoad = $('.FormPreviewNoLoad');
    var previewEmailLoaded = $('.previewEmailLoaded');
    var filesPreview = $('.previewEmailFiles');
    var inputFiles = $('#email-files');
    
    function readPreviewImages(input) {

    if (input.files && input.files[0]) {
        
        if(input.files.length > 0) {
            
            for(var i = 0; i < input.files.length; i++) {

                if((input.files[i].type.indexOf('image') + 1) > 0) {
                    filesPreview.html(filesPreview.html() + '<br /><img src="' + URL.createObjectURL(input.files[i]) + '" height="45px" /> - <span class="noSaveFile">Не сохранено</span>');
                } else {
                    filesPreview.html(filesPreview.html() + '<br /><u style="color:#069;">' + input.files[i].name + '</u> - <span class="noSaveFile">Не сохранено</span>');
                }
                
            }
            
        }
        
    }
    
    FormPreviewNoLoad.html(filesPreview.html());
    
    }
    
    inputFiles.change(function(){
    filesPreview.html('');
    readPreviewImages(this);
    });
    
    //

    //
    
    // Вложения
    
    // Удаление вложения
    $('.deleteFile').on('click', function(){

        if($(this).data("name")) {
            
                var dataName = $(this).data("name");
                
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + '$idEmail' + '&name=' + dataName,
                url  : '$actionLinkDelete',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                $(this).parent().remove();
                $('[data-prename="' + dataName + '"]').remove();
                
                } else {
                // Неудачно
                }
                
                }
                });
                    
        }
        
    });
    // Удаление вложения

// отправляем тестовое письмо администратору
$('.btn-success').on('click', function(){
    var emailCheck = prompt('Email для проверки:');
    
    if(emailCheck) {
        if(validateEmail(emailCheck)) {
            
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'email=' + emailCheck + '&title=' + textTitle + '&text=' + textMail + '&id=' + '$idEmail',
                url  : '$actionLinkEmail',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                $('#showModalPreview').modal('hide');
                alert('Письмо успешно отправлено администратору');
                } else {
                // Неудачно
                alert('Ошибка при отправке письма');
                }
                
                }
                });
            
        } else {
            alert('Введен некорректный Email');
        }
    } else {
        alert('Введен некорректный Email');
    }
    
});

JS;
$this->registerJs($script, View::POS_READY);

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $form yii\widgets\ActiveForm
 */
$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/email/add'] : ['/email/update', 'id' => $model->id],
    'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255, 'placeholder' => 'Введите название шаблона']) ?>

<?= $form->field($model, 'type')->dropDownList(['0' => 'Все компании' , '1' => 'Компании', '2' => 'Мойки', '3' => 'Сервисы', '4' => 'Шиномонтажи', '5' => 'Дезинфекции', '6' => 'Универсальные', '7' => 'Стоянки'], ['class' => 'form-control']) ?>

<?= $form->field($model, 'title')->textInput(['maxlength' => 255, 'placeholder' => 'Введите заголовок письма']) ?>

<?= $form->field($model, 'text')->textarea(['maxlength' => true, 'rows' => '13', 'placeholder' => 'Введите текст письма']) ?>

<?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

<?php

$numFiles = 0;
$filesEmail = '';

if(!$model->isNewRecord) {
    $pathFolderEmail = \Yii::getAlias('@webroot/files/email/' . $model->id . '/');

    if (file_exists($pathFolderEmail)) {
        foreach (FileHelper::findFiles($pathFolderEmail) as $file) {

            if(mb_strpos('_' . mime_content_type($pathFolderEmail . basename($file)), 'image') == 1) {
                $filesEmail .= '<div class="placeFile" style="display:block;" data-prename="' . basename($file) . '"><a href="' . '/files/email/' . $model->id . '/' . basename($file) .  '" target="_blank"><img src="' . '/files/email/' . $model->id . '/' . basename($file) . '" height="45px" /></a> - <span class="deleteFile" data-name="' . basename($file) . '">Удалить</span></div>';
            } else {
                $filesEmail .= '<div class="placeFile" style="display:block;" data-prename="' . basename($file) . '"><u><a href="' . '/files/email/' . $model->id . '/' . basename($file) .  '" target="_blank">' . basename($file) . '</a></u> - <span class="deleteFile" data-name="' . basename($file) . '">Удалить</span></div>';
            }

            $numFiles++;

        }
    }
}

if($numFiles > 0) {
    $filesEmail = '<b>Вложения:</b>' . $filesEmail;
} else {
    $filesEmail = '<b>Вложения:</b>';
}

echo "<div class=\"form-group\">
        <div class=\"col-sm-offset-3 col-sm-6\">
            <div class='FormPreviewLoad' style='font-size:14px; color:#000;'>" . $filesEmail . "</div>
        </div>
    </div>";

echo "<div class=\"form-group\">
        <div class=\"col-sm-offset-3 col-sm-6\" style=\"padding-bottom: 10px;\">
            <div class='FormPreviewNoLoad' style='font-size:14px; color:#000;'></div>
        </div>
    </div>";

?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?><span class="btn btn-warning btn-sm" style="margin-left:10px;">Предварительный просмотр</span>
        </div>
    </div>

<?php ActiveForm::end(); ?>

<?php
$modal = Modal::begin([
    'header' => '<h4>Предварительный просмотр письма</h4>',
    'id' => 'showModalPreview',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonPreview', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div class='previewEmailTitle' style='font-size:21px; color:#069; word-wrap: break-word;'></div>";
echo "<div class='previewEmailText' style='font-size:14px; color:#000; margin-top: 10px; word-wrap: break-word;'><b>Вложения:</b></div>";
echo "<div class='previewEmailLoaded' style='font-size:14px; color:#000; margin-top: 20px;'>" . $filesEmail . "</div>";
echo "<div class='previewEmailFiles' style='font-size:14px; color:#000;'></div>";
echo '<span class="btn btn-success btn-sm" style="margin-top:20px;">Отправить тестовое письмо администратору</span>';
Modal::end();
?>
