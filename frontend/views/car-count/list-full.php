<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\ActSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'Список ТС компании';
$script = <<< JS
    var count=[];
    $('.kv-grid-group').each(function(){
        var type=parseInt($(this).data('type-id'));

        if(isNaN(count[type])){
            count[type]=1
        }else{
            count[type]=count[type]+1;
        }
    });
    $('.kv-grid-group').each(function(){
        var type=parseInt($(this).data('type-id'));
        $(this).append(' - '+count[type]+' шт.');
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

echo GridView::widget([
    'dataProvider'       => $dataProvider,
    'filterModel'        => null,
    'floatHeaderOptions' => ['top' => '0'],
    'hover'              => false,
    'striped'            => false,
    'export'             => false,
    'summary'            => false,
    'emptyText'          => '',
    'filterSelector'     => '.ext-filter',
    'panel'              => [
        'type'    => 'primary',
        'heading' => 'Список ТС компании',
        'before'  => false,
        'footer'  => false,
        'after'   => false,
    ],
    'columns'            => [
        [
            'header' => '№',
            'class'  => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'mark_id',
            'content'   => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
            },
        ],
        'car_number',
        [
            'attribute'         => 'type_id',
            'group'             => true,
            'groupedRow'        => true,
            'groupOddCssClass'  => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
            'content'           => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : '';
            },
            'contentOptions'    => function ($model, $key, $index, $column) {
                return ['data-type-id' => $model->type_id];
            }
        ],
        [
            'attribute' => 'car.is_infected',
            'content'   => function ($data) {
                return $data->is_infected ? 'да' : 'нет';
            },
            'visible'   => $admin,
        ],
    ],
]);