<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel common\models\search\CarSearch
 */

$GLOBALS['MoveCheck'] = false;

if(isset($companyModel->parent_id)) {
    if ($companyModel->parent_id > 0) {
        $GLOBALS['MoveCheck'] = true;

        $css = ".glyphicon-sort {
font-size:17px;
}
.glyphicon-sort:hover {
cursor:pointer;
}";
        $this->registerCSS($css);

        $actionLink = Url::to('@web/car/movecar');
        $company_id = $companyModel->id;

$script = <<< JS

var car_id = 0;

// Удаляем ненужную кнопку открыть модальное окно
$('.hideButtonRemove').remove();

// открываем модальное окно перенести тс
$('.glyphicon-sort').on('click', function(){
car_id = $(this).data('id');

$('.removeList').html('<b>Номер ТС:</b> ' + $(this).data('number'));

$('#showModal').modal('show');
});

$('#save_new_company').on('click', function(){

                if(($('#new_company').val() > 0) && ($('#new_company').val() != $company_id) && (car_id > 0)) {

                $.ajax({
                type     :'POST',
                cache    : false,
                data:'id=' + car_id + '&company_from=' + '$company_id' + '&company_id=' + $('#new_company').val(),
                url  : '$actionLink',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                car_id = 0;
                alert('Успешно');
                window.location.reload();
                } else {
                // Неудачно
                car_id = 0;
                alert('Ошибка переноса');
                }
                
                }
                });
                
                }
    
});

JS;
        $this->registerJs($script, View::POS_READY);

    }
}

\yii\widgets\Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => '{items}',
    'emptyText' => '',
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute'          => 'mark_id',
            'content'            => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : 'error';
            },
            'filter'             => \common\models\Mark::getMarkList(),
            'filterInputOptions' => ['prompt' => 'выберите марку ТС', 'class' => 'form-control']
        ],
        'number',
        [
            'attribute'          => 'type_id',
            'content'            => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : 'error';
            },
            'filter'             => \common\models\Type::getTypeList(),
            'filterInputOptions' => ['prompt' => 'выберите тип ТС', 'class' => 'form-control']
        ],
        [
            'attribute' => 'is_infected',
            'content' => function ($data) {
                return $data->is_infected ? 'да' : 'нет';
            },
            'filter' => false,
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{remove} {update} {delete}',
            'options' => [
                'style' => 'width: 120px',
            ],
            'buttons' => [
                'remove' => function ($url, $model, $key) {
                    if($GLOBALS['MoveCheck'] == true) {
                        return '<span class="glyphicon glyphicon-sort" data-id="' . $model->id . '" data-number="' . $model->number . '"></span>';
                    } else {
                        return '';
                    }
                },
                'update' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/car/update', 'id' => $model->id]);
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/car/delete', 'id' => $model->id], [
                        'data-confirm' => "Are you sure you want to delete this item?",
                        'data-method' => "post",
                        'data-pjax' => "0",
                    ]);
                },
            ]
        ],
    ],
]);

\yii\widgets\Pjax::end();

if($GLOBALS['MoveCheck'] == true) {
    $modal = Modal::begin([
        'header' => '<h4>Перенести машину в другой филиал</h4>',
        'id' => 'showModal',
        'toggleButton' => ['label' => 'открыть окно', 'class' => 'btn btn-default hideButtonRemove', 'style' => 'display:none;'],
        'size' => 'modal-lg',
    ]);

    $arrCompany = \frontend\controllers\CompanyController::getCompanyParents($companyModel->id);

    echo "<div class='removeList' style='margin-bottom:15px; font-size:15px; color:#000;'></div>";

    echo Html::dropDownList("new_company", $companyModel->id, $arrCompany, ['id' => 'new_company', 'class' => 'form-control']);
    echo Html::buttonInput("Сохранить", ['id' => 'save_new_company', 'class' => 'btn btn-primary', 'style' => 'margin-top:20px; padding:7px 16px 6px 16px;']);

    Modal::end();
}