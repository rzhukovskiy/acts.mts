<?php

use common\models\Company;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @var $listCompany Company
 * @var $rows array[]
 */
?>

<?= $this->render('connect/_tabs') ?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Соединение программ
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['site/connect'],
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
        ])
        ?>
        <?php foreach ($listCompany as $company) { ?>
            <div class="form-group row">
                <label class="col-sm-3 control-label" style="text-align: right"><?= $company->name ?></label>

                <div class="col-sm-6">
                    <?= Html::dropDownList('Connection[' . $company->id . ']', $company->old_id, ArrayHelper::map($rows, 'id', 'name'), ['prompt' => 'нет', 'class' => 'form-control input-sm']) ?>
                </div>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>