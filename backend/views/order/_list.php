<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */

use yii\grid\GridView;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Архив записей
    </div>
    <div class="panel-body">
        <?= GridView::widget([
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
                    'attribute'          => 'mark_id',
                    'content'            => function ($data) {
                        return !empty($data->mark->name) ? Html::encode($data->mark->name) : 'error';
                    },
                ],
                'number',
                [
                    'attribute'          => 'type_id',
                    'content'            => function ($data) {
                        return !empty($data->type->name) ? Html::encode($data->type->name) : 'error';
                    },
                ],
                [
                    'attribute'          => 'card_id',
                    'content'            => function ($data) {
                        return !empty($data->card->number) ? Html::encode($data->card->number) : 'error';
                    },
                ],
                [
                    'attribute'          => 'start_at',
                    'format'             => ['date', 'php:d-m-Y H:i']
                ],
                [
                    'attribute'          => 'user_id',
                    'content'            => function ($data) {
                        return !empty($data->user->username) ? Html::encode($data->user->username) : 'нет';
                    },
                ],
                [
                    'attribute'          => 'company_id',
                    'content'            => function ($data) {
                        return !empty($data->company->name) ? Html::encode($data->company->name) : 'уккщк';
                    },
                ],
            ],
        ]); ?>
    </div>
</div>