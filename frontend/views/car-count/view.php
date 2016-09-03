<?php

use common\models\Company;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\widgets\Pjax;

/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CarSearch
 * @var $typeModel \common\models\Type
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'ТС типа «' . Html::encode($typeModel->name) . '»';

$filters = null;
if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'company_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {
    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'company_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter']);
}
?>
<div class="car-count-view">
    <div class="panel panel-primary">
        <div class="panel-heading"><?= $this->title ?></div>
        <div class="panel-body">
            <?php
            Pjax::begin();
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'id' => 'car-count-view',
                'layout' => "{items}",
                'summary' => false,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'emptyText' => '',
                'filterUrl' => 'list',
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
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'mark.name',
                        'label' => 'Марка',
                    ],
                    [
                        'attribute' => 'number',
                    ],
                    [
                        'attribute' => 'is_infected',
                        'content' => function ($data) {
                            return $data->is_infected ? 'Да' : 'Нет';
                        },
                        'visible' => $admin,
                    ],
                ]
            ]);

            Pjax::end();
            ?>
        </div>
    </div>
</div>
