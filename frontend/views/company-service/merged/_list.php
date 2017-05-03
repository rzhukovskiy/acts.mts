<?php

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $type int
 */

use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use common\models\Service;

$colorPluginOptions =  [
    'showPalette' => true,
    'showPaletteOnly' => true,
    'showSelectionPalette' => true,
    'showAlpha' => false,
    'allowEmpty' => false,
    'preferredFormat' => 'name',
    'palette' => [
        [
            "white", "black", "grey", "silver", "gold", "brown",
        ],
        [
            "red", "orange", "yellow", "indigo", "maroon", "pink"
        ],
        [
            "blue", "green", "violet", "cyan", "magenta", "purple",
        ],
    ]
];

$columns = [
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
];

foreach (Service::findAll(['type' => $type]) as $service) {
    $columns[] = [
        'header' => $service->description,
        'attribute' => 'price',
        'class'=>'kartik\grid\EditableColumn',
        'readonly'=> false,
        'editableOptions'=>[
            'header'=>$service->description,
            'formOptions' => ['action' => ['/company/editprice?id=' . 5 . '&type=' . $service->type . '&idService=' . $service->id]],
            'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            'options'=>[
                'pluginOptions'=>['min' => 0, 'max' => 99999]
            ]
        ],
        'value' => function ($data) use($service) {
            return $data->getPriceForService($service->id);
        },
    ];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'emptyText' => '',
    'tableOptions' => ['class' => 'table table-bordered'],
    'columns' => $columns,
]);