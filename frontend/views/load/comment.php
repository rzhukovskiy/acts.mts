<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $admin null|bool
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Tabs;

$this->title = 'Комментарий';


?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->isNewRecord ? 'Добавление комментария' : 'Редактирование комментария' ?>
    </div>
    <div class="panel-body">
        <?php

        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['load/comment', 'id' => $id, 'type' => $type, 'period' => $period, 'company' => $company] : ['load/comment', 'id' => $id, 'type' => $type, 'period' => $period, 'company' => $company],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>

       <?= ($form->field($model, 'comment')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите комментарий']));?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>