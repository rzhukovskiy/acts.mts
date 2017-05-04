<?php

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $type int
 */

use kartik\grid\GridView;
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'emptyText' => '',
    'tableOptions' => ['class' => 'table table-bordered'],
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'type_id',
            'value' => function ($data) {
                return $data->type->name;
            },
        ],
        [
            'attribute' => 'service_id',
            'value' => function ($data) {
                return $data->service->description;
            },
        ],
        [
            'attribute' => 'price',
            'class'=>'kartik\grid\EditableColumn',
            'readonly'=> false,
            'options' => [
                'style' => 'width: 250px',
            ],
            'editableOptions'=> function ($data) {
                return [
                    'formOptions' => ['action' => ['/company/editprice?service_id=' . $data->id]],
                    'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
                    'options'=>[
                        'pluginOptions'=>['min' => 0, 'max' => 99999],
                    ]
                ];},
        ],
    ],
]);