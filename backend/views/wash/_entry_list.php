<?php

/**
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */
use yii\grid\GridView;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Запись ТС на мойку
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $searchModel->search([]),
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'start_at',
                    'value' => function ($model) {
                        return date('H:i', $model->start_at);
                    },
                ],
                [
                    'attribute' => 'end_at',
                    'value' => function ($model) {
                        return date('H:i', $model->end_at);
                    },
                ],
                [
                    'header' => 'Карта',
                    'attribute' => 'card.number',
                    'value' => function ($model) {
                        return $model->card->number;
                    },
                ],
                [
                    'header' => 'Марка ТС',
                    'attribute' => 'mark.name',
                    'value' => function ($model) {
                        return $model->mark->name;
                    },
                ],
                'number',
                [
                    'header' => 'Тип ТС',
                    'attribute' => 'type.name',
                    'value' => function ($model) {
                        return $model->type->name;
                    },
                ],
            ],
        ]);
        ?>
    </div>
</div>