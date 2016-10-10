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
        <?php
        $dataProvider = $searchModel->search([]);
        $dataProvider->query->andWhere(['act_id' => null])->andWhere(['is not', 'card_id', null])->andWhere(['>', 'end_at', time()]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
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
                [
                    'header' => 'Карта',
                    'attribute' => 'card.number',
                    'value' => function ($model) {
                        return $model->card->number;
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'contentOptions' => ['style' => 'min-width: 80px'],
                ],
            ],
        ]);
        ?>
    </div>
</div>