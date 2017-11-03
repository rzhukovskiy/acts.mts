<?php

/**
 * @var $model \common\models\Company
 * @var $type integer
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Company;
use common\models\Service;
use common\models\Requisites;
use yii\base\DynamicModel;
use yii\web\View;
use yii\helpers\Url;

// Стиль кнопки удалить лого компании
$css = ".deleteLogo {
margin-left: 10px;
font-size: 14px;
color: #d9534f;
text-decoration: underline;
}
.deleteLogo:hover {
margin-left: 10px;
font-size: 14px;
color: #d9534f;
text-decoration: none;
cursor:pointer;
}";
$this->registerCSS($css);

$actionLinkDeleteLogo = Url::to('@web/company/deletelogo');
$company_id = $model->id;

$script = <<< JS

// Клик удалить лого компании
$('.deleteLogo').on('click', function(){
    var checkDeleteLogo = confirm("Вы уверены что хотите удалить логотип компании?");
    
    if(checkDeleteLogo == true) {
        
        $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + '$company_id',
                url  : '$actionLinkDeleteLogo',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                location.reload();
                
                } else {
                // Неудачно
                }
                
                }
                });
        
    }
    
});

JS;
$this->registerJs($script, View::POS_READY);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->isNewRecord ? 'Добавление мойки' : 'Редактирование мойки ' . $model->name?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['company/create', 'type' => $type] : ['company/update', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'parent_id')->dropdownList(
            Company::getSortedItemsForDropdown(),
            ['prompt'=>'выберите компанию']
        ) ?>
        <?= $form->field($model, 'address') ?>

        <?php

        // Проверка существования картинки и вывод превью
        $haveLogoCompany = false;

        $prefixHttp = '';

        if(strpos(Url::to('@frontWeb'), 'http') === false) {
            $prefixHttp = 'http://';
        }

        $linkLogoCompany = $prefixHttp . \Yii::getAlias('@frontWeb/files/logos/' . $model->id . '.jpg?' . time());
        $linkLogoPath = \Yii::getAlias('@webroot/files/logos/' . $model->id . '.jpg');

        if (file_exists($linkLogoPath)) {
            $haveLogoCompany = true;
        }

        if($haveLogoCompany == true) {

            ?>

            <!-- Вывод загруженного логотипа -->
            <div class="form-group field-dynamicmodel-logo">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-6">
                    <?= '<a href="' . $linkLogoCompany . '" target="_blank"><img width="40px" src="' . $linkLogoCompany . '" /></a>' ?>
                    <span class="deleteLogo">Удалить</span>
                </div>
            </div>
            <!-- Вывод загруженного логотипа -->

            <!-- Загрузка лого компании -->
            <?php

        }

        if(!$model->isNewRecord) {
            $modelAddAttach = new DynamicModel(['logo']);
            $modelAddAttach->addRule(['logo'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 1, 'extensions' => 'jpg, jpeg', 'maxSize' => 1536000, 'tooBig' => 'Максимальный размер файла 1.5MB']);

            echo $form->field($modelAddAttach, 'logo')->fileInput(['multiple' => false])->label('Логотип компании');
        }

        ?>
        <!-- Загрузка лого компании -->

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <span data-toggle="collapse" data-target="#details" class="pseudo">Подробнее</span>
            </div>
        </div>

        <div id="details" class="collapse">
            <?= $form->field($model, 'schedule')->checkbox([], false) ?>
            <?php

            if($model->isNewRecord) {
                $model->is_act_sign = 1;
            }

            echo $form->field($model, 'is_act_sign')->radioList([
                '0' => 'Нет',
                '1' => 'Подпись и печать',
                '2' => 'Только подпись',
            ]); ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-6">
                    <strong>Для актов</strong>
                </div>
            </div>
            <?= $form->field($model, 'director') ?>
            <?php
                $id = $model->type;
                $type = Service::$listType[$model->type];
                $existed = $model->isNewRecord ? null : Requisites::findOne(['company_id' => $model->id, 'type' => $id])
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Договор</label>
                <div class="col-sm-6">
                    <?= Html::textInput("Company[requisitesList][$id][Requisites][contract]", $existed ? $existed->contract : '', ['class' => 'form-control input-sm'])?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Текст заголовка</label>
                <div class="col-sm-8">
                    <?= Html::textarea("Company[requisitesList][$id][Requisites][header]", $existed ? $existed->header : '', ['rows' => 10, 'class' => 'form-control input-sm'])?>
                </div>
            </div>
            <?= Html::hiddenInput("Company[requisitesList][$id][Requisites][type]", $id)?>
            <?= Html::hiddenInput("Company[requisitesList][$id][Requisites][id]", $existed ? $existed->id : '')?>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>