<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $admin null|bool
 */

use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use common\models\ExpenseCompany;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->isNewRecord ? $model->type == 1 ? 'Добавление сотрудника' : 'Добавление организации' : 'Редактирование' ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['expense/addexpensecomp', 'type' => $model->type] : ['expense/updateexpense', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>

       <?= $form->field($model, 'name')->input('text', ['class' => 'form-control', 'placeholder' => $model->type == 1 ? 'ФИО' : 'Наименование организации']);?>


        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>



<div class="panel panel-primary">
    <div class="panel-heading">
        Список
    </div>
    <div class="panel-body">
        <?php

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'vAlign'=>'middle',
                    'class' => 'kartik\grid\SerialColumn'
                ],

                [
                    'attribute' => 'name',
                    'header' => $model->type == 1 ? 'ФИО' : 'Наименование организации',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'contentOptions' => ['style' => 'min-width: 100px'],
                    'value' => function ($data) {

                        if ($data->name) {
                            return $data->name;
                        } else {
                            return '-';
                        }

                    },
                ],

                [
                    'class' => 'kartik\grid\ActionColumn',
                    'header' => 'Действие',
                    'vAlign'=>'middle',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/expense/expensecomp', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>