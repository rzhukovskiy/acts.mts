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
            'layout' => '{items}',
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
                    'value' => function ($data) {
                        return $data->cardsAsString;
                    },
                ],
                [
                    'label' => 'Количество машин',
                    'value' => function ($data) {
                        return $data->carsCount;
                    },
                ],
                [
                    'label' => 'Количество прицепов',
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