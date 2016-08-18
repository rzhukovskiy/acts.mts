<?php

use yii\bootstrap\Html;
use yii\grid\GridView;

/**
 * @var $this \yii\web\View
 * @var $carByTypes \yii\data\ActiveDataProvider
 *
 */

$this->title = 'Список типов ТС';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="car-count-index">
    <h1></h1>
    <div class="panel panel-primary">
        <div class="panel-heading"><?= $this->title ?></div>
        <div class="panel-body">
            <?php
            echo GridView::widget([
                'dataProvider' => $carByTypes,
                'id' => 'car-count-штвуч',
                'layout' => "{summary}\n{items}\n{pager}",
                'summary' => false,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'type.name',
                        'content' => function ($data) {
                            return $data->type->name;
                        },
                    ],
                    [
                        'attribute' => 'carsCountByType',
                        'label' => 'Кол-во'
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $data, $key) {
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'type' => $data->type->id]);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
