<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $userData array
 */
use yii\grid\GridView;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
    </div>
    <div class="panel-body">
        <?php
        if ($admin) {
            echo $this->render('_selector', [
                'type' => $type,
                'userData' => $userData,
                'searchModel' => $searchModel,
            ]);
        }
        
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                'address',
                'name',
                [
                    'attribute' => 'fullAddress',
                    'value'     => function ($model) {
                        return ($model->fullAddress) ? $model->fullAddress : 'не задан';
                    }
                ],
                [
                    'attribute' => 'offer.communication_at',
                    'value' => function($model) {
                        return !empty($model->offer->communication_at) ? date('d-m-Y H:i', $model->offer->communication_at) : 'Не указана';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{delete}',
                    'contentOptions' => ['style' => 'min-width: 70px'],
                ],
            ],
        ]);
        ?>
    </div>
</div>