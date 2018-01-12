<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Добавление';

echo Tabs::widget([
    'items' => [
        ['label' => 'Новые', 'url' => ['company/tenderownerlist?win=1']],
        ['label' => 'В работе', 'url' => ['company/tenderownerlist?win=0']],
        ['label' => 'Добавление', 'url' => ['company/tenderowneradd'], 'active' => Yii::$app->controller->action->id == 'tenderowneradd'],
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
    'action' => $model->isNewRecord ? ['/company/tenderowneradd'] : ['/company/tenderownerupdate'],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
    'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
    'labelOptions' => ['class' => 'col-sm-3 control-label'],
    'inputOptions' => ['class' => 'form-control input-sm'],
    ],
    ]); ?>

    <?= $form->field($model, 'text')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите текст']) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
    </div>
</div>