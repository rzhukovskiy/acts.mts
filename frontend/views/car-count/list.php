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
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'carsCountByType',
                        'label' => 'Кол-во'
                    ],
                    [
                        'attribute' => 'type.name',
                        'content' => function ($data) {
                            return Html::a($data->type->name, ['car-count/view', 'type' => $data->type->id]);
                        },
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
