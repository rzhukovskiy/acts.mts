<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $searchModel common\models\search\CompanyDriverSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$script = <<< JS

$('.uploadPhonesButt').on('click', function(){
// Инициируем нажатие на форму выбора файла
$(".uploadPhones").trigger("click");
});

// Отправляем файл если он был выбран
form = $(".uploadPhonesForm"), upload = $(".uploadPhones");
upload.change(function(){
form.submit();
});

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= \Yii::$app->controller->action->id == 'driver' ? $searchModel->company->name : $model->name ?> :: <?= \Yii::$app->controller->action->id == 'driver' ? 'Водители' : 'ТС без водителей' ?>
        <div class="header-btn pull-right">
            <?php

            $company_id = 0;

            if(\Yii::$app->controller->action->id == 'driver') {
                $company_id = $searchModel->company_id;
                echo Html::a('Список ТС без водителей', ['company/undriver', 'id' => $company_id], ['class' => 'btn btn-danger btn-sm', 'style' => 'margin-right:10px;']);
                echo Html::a('Выгрузить список ТС', ['company-driver/carsexcel', 'id' => $company_id], ['class' => 'btn btn-success btn-sm', 'style' => 'margin-right:10px;']);
            } else {
                $company_id = $model->id;
                echo Html::a('Водители', ['company/driver', 'id' => $company_id], ['class' => 'btn btn-danger btn-sm', 'style' => 'margin-right:10px;']);
                echo Html::a('Выгрузить список ТС', ['company-driver/carsexcel', 'id' => $company_id, 'undriver' => true], ['class' => 'btn btn-success btn-sm', 'style' => 'margin-right:10px;']);
            }

            // Форма загрузки файла со списком
            echo '<span class="btn btn-warning btn-sm uploadPhonesButt" style="margin-right:10px;">Загрузить заполненный список</span>';
            echo Html::beginForm(['company-driver/upload', 'company_id' => $company_id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'uploadPhonesForm', 'style' => 'display:none;']);
            echo Html::fileInput("uploadPhones", '',['accept' => '.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel', 'class' => 'uploadPhones', 'style' => 'display:none;']);
            echo Html::endForm();
            // Форма загрузки файла со списком

            echo Html::a('Добавить водителя', ['company-driver/create', 'company_id' => $company_id], ['class' => 'btn btn-danger btn-sm']);

            ?>

        </div>
    </div>

    <?php

    $GLOBALS['arrTypes'] = $arrTypes;
    $GLOBALS['arrMarks'] = $arrMarks;

    if(\Yii::$app->controller->action->id == 'driver') {

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'filterSelector' => '.ext-filter',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'phone',
                ],
                [
                    'header' => 'Номер ТС',
                    'attribute' => 'car.number',
                ],
                [
                    'header' => 'Марка ТС',
                    'value' => function ($data) {

                        if (isset($data['car']['mark_id'])) {
                            $idMark = $data['car']['mark_id'];
                            return $GLOBALS['arrMarks'][$idMark];
                        }

                    },
                ],
                [
                    'header' => 'Тип ТС',
                    'value' => function ($data) {

                        if (isset($data['car']['type_id'])) {
                            $idType = $data['car']['type_id'];
                            return $GLOBALS['arrTypes'][$idType];
                        }

                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'contentOptions' => ['align' => 'center'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/company-driver/update', 'id' => $model->id]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company-driver/delete', 'id' => $model->id], [
                                'data-confirm' => "Are you sure you want to delete this item?",
                                'data-method' => "post",
                                'data-pjax' => "0",
                            ]);
                        },
                    ]
                ],
            ],
        ]);

    } else {

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'filterSelector' => '.ext-filter',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'header' => 'Номер ТС',
                    'attribute' => 'number',
                ],
                [
                    'header' => 'Марка ТС',
                    'value' => function ($data) {
                        return $GLOBALS['arrMarks'][$data->mark_id];
                    },
                ],
                [
                    'header' => 'Тип ТС',
                    'value' => function ($data) {
                        return $GLOBALS['arrTypes'][$data->type_id];
                    },
                ],
            ],
        ]);

    }

    ?>

</div>
