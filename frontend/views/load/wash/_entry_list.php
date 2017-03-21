<?php

/**
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */
use common\components\ArrayHelper;
use yii\grid\GridView;
use yii\helpers\Html;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Записи от Международного Транспортного Сервиса
    </div>
    <div class="panel-body">
        <?php
        $dataProvider = $searchModel->search([]);
        $dataProvider->query->andWhere(['act_id' => null])->andWhere(['is not', 'card_id', null]);
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
                    'contentOptions' => [
                        'class' => 'entry-time',
                    ]
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
                [
                    'header' => 'Услуга',
                    'value' => function ($model, $key, $index, $column) use ($serviceList) {
                        $fields = Html::beginForm(['act/create', 'type' => $model->service_type]) .
                            Html::hiddenInput("Act[serviceList][0][amount]", 1) .
                            Html::hiddenInput("Act[serviceList][0][price]", 0) .
                            Html::hiddenInput("entry_id", $model->id) .
                            '<div class="input-group" style="width: 100%;">' .
                            Html::dropDownList('Act[serviceList][0][service_id]', [], ArrayHelper::perMutate($serviceList), ['class' => 'form-control', 'style' => 'width: 60%']) .
                            Html::submitButton('Оформить', ['class' => 'form-control btn btn-primary', 'style' => 'width: 40%']) .
                            '</div>' .
                            Html::endForm();
                        return $model->number ? $fields : '';
                    },
                    'format' => 'raw',
                ],
            ],
        ]);
        ?>
    </div>
</div>