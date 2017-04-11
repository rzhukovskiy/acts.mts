<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $userList User[]
 */
use common\models\Company;
use kartik\grid\GridView;
use common\models\User;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
        </div>
    </div>

    <div class="panel-body">
        <table border="0" width="100%" style="margin-top:20px;">
            <tr><td width="25%" style="padding-left: 40px; font-size:15px;">Выберите типы ТС:</td>
                <td width="25%" style="padding-left: 30px; font-size:15px;">Выберите типы услуг:</td>
                <td width="25%" style="padding-left: 25px; font-size:15px;">Выберите город:</td>
                <td width="25%" style="padding-left: 25px; font-size:15px;"></td></tr>
        </table>
        <?php
        $form = ActiveForm::begin([
            'id' => 'offer-form',
            'method' => 'get',
            'options' => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin: 20px 0;'],
            'fieldConfig' => [
                'template' => '{input}',
                'inputOptions' => ['class' => 'form-control input-sm'],
                'options' => ['class' => 'col-sm-3'],
            ],
        ]) ?>
        <?= $form->field($searchModel, 'cartypes')->dropdownList($listCar, ['multiple' => 'true', 'size' => '10']); ?>
        <?= $form->field($searchModel, 'services')->dropdownList($listService, ['multiple' => 'true', 'size' => '10']); ?>
        <?= $form->field($searchModel, 'address')->dropdownList($listCity, ['multiple' => 'true', 'size' => '10']); ?>
        <?= Html::submitButton('Применить', ['class' => 'btn btn-primary btn-sm']) ?>
        <?php if(strlen(Yii::$app->request->get('ad')) > 0) {
            echo Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 10px;">Сбросить</span>', '/company/offer?ad=' . Yii::$app->request->get('ad') . '&type=' . Yii::$app->request->get('type'));
        } else {
            echo Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 10px;">Сбросить</span>', '/company/offer?type=' . Yii::$app->request->get('type'));
        }  ?>

        <?php ActiveForm::end() ?>
    </div>

    <div class="panel-body">

    </div>
</div>