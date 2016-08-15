<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список актов
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
                [
                    'attribute' => 'served_at',
                    'value' => function($data) {
                        return date('d-m-Y H:i', $data->served_at);
                    },
                ],
                [
                    'attribute' => 'client_id',
                    'value' => function($data) {
                        return isset($data->client) ? $data->client->name : 'error';
                    },
                ],
                [
                    'attribute' => 'card_id',
                    'value' => function($data) {
                        return isset($data->card) ? $data->card->number : 'error';
                    },
                ],
                'number',
                'extra_number',
                [
                    'attribute' => 'mark_id',
                    'value' => function($data) {
                        return isset($data->mark) ? $data->mark->name : 'error';
                    },
                ],
                [
                    'attribute' => 'type_id',
                    'value' => function($data) {
                        return isset($data->type) ? $data->type->name : 'error';
                    },
                ],
                'income',
                'check',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}'
                ],
            ],
        ]);
        ?>
    </div>
</div>