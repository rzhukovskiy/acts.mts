<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $type integer
 */

use yii\grid\GridView;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Список компаний</h3>
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
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
                        return $data->tracksCount;
                    },
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]);
        ?>
    </div>
</div>