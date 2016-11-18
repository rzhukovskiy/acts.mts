<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $form yii\widgets\ActiveForm
 */
$form = ActiveForm::begin([
    'action' => ['/company-member/send', 'id' => $model->id],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= Html::label('Тема', ['class' => 'col-sm-3 control-label']) ?>
<?= Html::textInput('topic', '', ['class' => 'form-control input-sm']) ?>
<?= Html::label('Сообщение', ['class' => 'col-sm-3 control-label']) ?>
<?= Html::textarea('text', '', ['class' => 'form-control input-sm', 'rows' => 5]) ?>

    <div class="form-group">
        <div class="col-sm-6">
            <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>