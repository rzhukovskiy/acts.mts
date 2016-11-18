<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $userData array
 */
use common\models\Company;
use yii\grid\GridView;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', [
                'company/create',
                'Company[type]' => $searchModel->type,
                'Company[status]' => Company::STATUS_NEW,
            ], ['class' => 'btn btn-danger btn-sm']) ?>
        </div>
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
                [
                    'header' => 'Организация',
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'fullAddress',
                    'value'     => function ($model) {
                        return ($model->fullAddress) ? $model->fullAddress : 'не задан';
                    }
                ],
                [
                    'header' => 'Связь',
                    'attribute' => 'offer.communication_at',
                    'value' => function($model) {
                        return !empty($model->offer->communication_at) ? date('d-m-Y H:i', $model->offer->communication_at) : 'Не указана';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 70px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/update', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>