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
        <?= $searchModel->company->name ?> :: Водители
        <div class="header-btn pull-right">
            <?= Html::a('Выгрузить список ТС', ['company-driver/carsexcel', 'id' => $searchModel->company_id], ['class' => 'btn btn-success btn-sm']) ?>

            <!-- Форма загрузки файла со списком-->
            <?= '<span class="btn btn-warning btn-sm uploadPhonesButt">Загрузить заполненный список</span>' ?>
            <?= Html::beginForm(['company-driver/upload', 'company_id' => $searchModel->company_id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'uploadPhonesForm', 'style' => 'display:none;']); ?>
            <?= Html::fileInput("uploadPhones", '',['accept' => '.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel', 'class' => 'uploadPhones', 'style' => 'display:none;']); ?>
            <?= Html::endForm(); ?>
            <!-- END Форма загрузки файла со списком-->

            <?= Html::a('Добавить водителя', ['company-driver/create', 'company_id' => $searchModel->company_id], ['class' => 'btn btn-danger btn-sm']) ?>
        </div>
    </div>

    <?php

    $GLOBALS['arrTypes'] = $arrTypes;
    $GLOBALS['arrMarks'] = $arrMarks;

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

                    if(isset($data['car']['mark_id'])) {
                        $idMark = $data['car']['mark_id'];
                        return $GLOBALS['arrMarks'][$idMark];
                    }

                },
            ],
            [
                'header' => 'Тип ТС',
                'value' => function ($data) {

                    if(isset($data['car']['type_id'])) {
                        $idType = $data['car']['type_id'];
                        return  $GLOBALS['arrTypes'][$idType];
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

    ?>

</div>
