<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 */

use yii\grid\GridView;
use common\models\Act;
use common\models\Company;
use common\models\Card;
use common\models\Mark;
use common\models\Type;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список актов
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'served_at',
                    'filter' => Act::getPeriodList(),
                    'value' => function($data) {
                        return date('d-m-Y H:i', $data->served_at);
                    },
                ],
                [
                    'attribute' => 'partner_id',
                    'filter' => Company::find()->select(['name', 'id'])->indexBy('id')->column(),
                    'value' => function($data) {
                        return isset($data->partner) ? $data->partner->name : 'error';
                    },
                ],
                [
                    'attribute' => 'card_id',
                    'filter' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
                    'value' => function($data) {
                        return isset($data->card) ? $data->card->number : 'error';
                    },
                ],
                'number',
                'extra_number',
                [
                    'attribute' => 'mark_id',
                    'filter' => Mark::find()->select(['name', 'id'])->indexBy('id')->column(),
                    'value' => function($data) {
                        return isset($data->mark) ? $data->mark->name : 'error';
                    },
                ],
                [
                    'attribute' => 'type_id',
                    'filter' => Type::find()->select(['name', 'id'])->indexBy('id')->column(),
                    'value' => function($data) {
                        return isset($data->type) ? $data->type->name : 'error';
                    },
                ],
                'expense',
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