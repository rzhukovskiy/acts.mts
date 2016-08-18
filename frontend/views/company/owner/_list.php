<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список компаний
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'tableOptions' => ['class' => 'table table-bordered'],
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                'name',
                'address',
                [
                    'label' => 'Количество карт',
                    'contentOptions' => ['style' => 'max-width:200px'],
                    'options' => ['style' => 'max-width:200px'],
                    'value' => function ($data) {
                        return count($data->cards) . ' (' . $data->cardsAsString . ')';
                    },
                ],
                [
                    'label' => 'Машин',
                    'value' => function ($data) {
                        return $data->carsCount;
                    },
                ],
                [
                    'label' => 'Прицепов',
                    'value' => function ($data) {
                        return $data->trucksCount;
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}'
                ],
            ],
        ]);
        ?>
    </div>
</div>