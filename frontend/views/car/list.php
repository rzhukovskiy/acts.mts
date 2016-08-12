<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\CarSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Cars';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_tabs');
?>
<div class="car-index">
    <div class="panel panel-primary">
        <div class="panel-heading">Машины</div>
        <div class="panel-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'summary' => false,
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],

                    [
                        'attribute' => 'company_id',
                        'content' => function ($data) {
                            return Html::encode($data->company->name);
                        },
                        'filter' => false,
                    ],
                    'number',
                    [
                        'attribute' => 'mark_id',
                        'content' => function ($data) {
                            return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
                        },
                        'filter' => false,
                    ],
                    [
                        'attribute' => 'type_id',
                        'content' => function ($data) {
                            return !empty($data->type->name) ? Html::encode($data->type->name) : '';
                        },
                        'filter' => false,
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
