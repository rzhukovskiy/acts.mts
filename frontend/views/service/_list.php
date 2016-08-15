<?php

use yii\grid\GridView;
use common\models\Service;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список компаний
    </div>
    <div class="panel-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                'description',
                [
                    'attribute' => 'type',
                    'value' => function ($data) {
                        return Service::$listType[$data->type]['ru'];
                    },
                ],
                [
                    'attribute' => 'is_fixed',
                    'value' => function ($data) {
                        return $data->is_fixed ? 'Да' : 'Нет';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}'
                ],
            ],
        ]); ?>
    </div>
</div>
