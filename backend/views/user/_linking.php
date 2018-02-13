<?php
use yii\grid\GridView;
use yii\helpers\Html;
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $newUser \common\models\User
 */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'user_id',
        ],
        [
            'attribute' => 'company_id',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}{delete}',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/user/update', 'id' => $model->id]);
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/user/update', 'id' => $model->id]);
                },
            ]
        ],
    ],
]);

?>