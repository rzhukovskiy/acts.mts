<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $carByTypes \yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CarSearch
 * @var $companyDropDownData array
 */

$this->title = 'Список типов ТС';

?>
<div class="car-count-index">
    <?php if ($admin || !empty(Yii::$app->user->identity->company->children)) { ?>
        <div class="panel panel-primary">
            <div class="panel-heading">
                Поиск
            </div>
            <div class="panel-body">
                <?php echo $this->render('_search', ['model' => $searchModel, 'companyDropDownData' => $companyDropDownData, 'type' => null]); ?>
            </div>
        </div>
    <?php } ?>
    <div class="panel panel-primary">
        <div class="panel-heading"><?= $this->title ?></div>
        <div class="panel-body">
            <?php
            echo GridView::widget([
                'dataProvider' => $carByTypes,
                'id' => 'car-count-index',
                'layout' => "{items}",
                'hover' => false,
                'striped' => false,
                'export' => false,
                'showPageSummary' => true,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'kartik\grid\SerialColumn',
                        'pageSummary' => 'Всего',
                    ],
                    [
                        'attribute' => 'type.name',
                        'content' => function ($data) {
                            return $data->type->name;
                        },
                    ],
                    [
                        'attribute' => 'carsCountByType',
                        'label' => 'Кол-во',
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
                    ],
                    [
                        'header' => '',
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $data, $key) use ($searchModel) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'type' => $data->type->id, 'CarSearch[company_id]' => $searchModel->company_id]);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
