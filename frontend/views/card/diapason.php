<?php

use common\models\Card;
use kartik\grid\GridView;

/**
 * @var $this yii\web\View
 * @var $arr array
 */

$this->title = 'Карты свободные и занятые';

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'export'       => false,
    'panel'        => [
        'type'    => 'primary',
        'heading' => 'Диапазон карт',
        'before'  => false,
        'footer'  => false,
        'after'   => false,
    ],
    'columns'      => [
        [
            'attribute' => 'company_name',
            'header'    => 'Компания',
        ],
        [
            'attribute' => 'val',
            'header'    => 'Значение',
        ],
        [
            'attribute' => 'type',
            'header'    => 'Тип',
            'content'   => function ($data) {
                return Card::$cardType[$data['type']];
            },
        ],

    ],
]);
