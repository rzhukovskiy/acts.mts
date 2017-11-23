<?php

use common\models\Company;
use kartik\grid\GridView;
use yii\bootstrap\Html;

/**
 * @var $this \yii\web\View
 * @var $carByTypes \yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CarSearch
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'Список типов ТС';

$filters = null;
if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'company_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {

    // ищем дочерние дочерних
    $queryPar = Company::find()->where(['parent_id' => Yii::$app->user->identity->company_id])->select('id')->column();

    $arrParParIds = [];

    for ($i = 0; $i < count($queryPar); $i++) {

        $arrParParIds[] = $queryPar[$i];

        $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

        for ($j = 0; $j < count($queryParPar); $j++) {
            $arrParParIds[] = $queryParPar[$j];
        }

    }

    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'company_id', Company::find()->active()
            ->where(['id' => $arrParParIds])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter']);
}
?>
<div class="car-count-index">
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
                'filterSelector' => '.ext-filter',
                'beforeHeader' => $filters ? [
                    [
                        'columns' => [
                            [
                                'content' => $filters,
                                'options' => ['colspan' => 4, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
                            ],
                        ],
                        'options' => ['class' => 'filters extend-header'],
                    ],
                    [
                        'columns' => [
                            [
                                'content' => '&nbsp',
                                'options' => [
                                    'colspan' => 4,
                                ]
                            ]
                        ],
                        'options' => ['class' => 'kv-group-header'],
                    ],
                ] : null,
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
