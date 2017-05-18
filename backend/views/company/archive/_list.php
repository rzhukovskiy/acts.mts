<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */
use common\models\Company;
use kartik\grid\GridView;
use yii\helpers\Html;

$GLOBALS["typeName"] = '';

switch ($type) {
    case 1:
        $GLOBALS["typeName"] = 'Компаний';
        break;
    case 2:
        $GLOBALS["typeName"] = 'Моек';
        break;
    case 3:
        $GLOBALS["typeName"] = 'Сервисов';
        break;
    case 4:
        $GLOBALS["typeName"] = 'Шиномонтажей';
        break;
    case 5:
        $GLOBALS["typeName"] = 'Дезинфекций';
        break;
    case 6:
        $GLOBALS["typeName"] = 'Универсальных';
        break;
}

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', [
                'company/create',
                'Company[type]' => $searchModel->type,
                'Company[status]' => Company::STATUS_ARCHIVE
            ], ['class' => 'btn btn-danger btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'showPageSummary' => true,
            'emptyText' => '',
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'pageSummary' => 'Всего',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'address',
                    'group' => true,
                    'groupedRow' => true,
                    'groupOddCssClass' => 'kv-group-header',
                    'groupEvenCssClass' => 'kv-group-header',
                    'value' => function ($data) {
                        return $data->address;
                    },
                    'groupFooter' => function ($model, $key, $index) {

                        return [
                            'content' => [
                                2 => $GLOBALS["typeName"] . ':',
                                3 => GridView::F_COUNT,
                            ],
                            'contentFormats' => [
                                3 => ['format' => 'number'],
                            ],
                            'contentOptions' => [
                                2 => ['style' => 'font-weight: bold'],
                            ],
                        ];
                    },
                ],
                [
                    'header' => 'Организация',
                    'attribute' => 'name',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_COUNT,
                    'contentOptions' => function ($data) {
                        return ($data->status == Company::STATUS_ACTIVE) ? ['style' => 'font-weight: bold'] : [];
                    },
                ],
                [
                    'attribute' => 'fullAddress',
                    'content' => function ($data) {
                        return ($data->fullAddress) ? $data->fullAddress : 'не задан';
                    }
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
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