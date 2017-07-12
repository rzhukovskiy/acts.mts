<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */
use common\models\Company;
use kartik\grid\GridView;
use yii\helpers\Html;

// Подкатегории для сервиса
$requestSupType = 0;
if($searchModel->type == 3) {
    if(Yii::$app->request->get('sub')) {
        $requestSupType = Yii::$app->request->get('sub');
    }
}
// Подкатегории для сервиса

$GLOBALS["typeName"] = '';

switch ($type) {
    case 1:
        $GLOBALS["typeName"] = 'компаний:';
        break;
    case 2:
        $GLOBALS["typeName"] = 'моек:';
        break;
    case 3:
        $GLOBALS["typeName"] = 'сервисов:';
        break;
    case 4:
        $GLOBALS["typeName"] = 'шиномонтажей:';
        break;
    case 5:
        $GLOBALS["typeName"] = 'дезинфекций:';
        break;
    case 6:
        $GLOBALS["typeName"] = 'универсальных:';
        break;
}

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
            <?php

            if(($searchModel->type != 3) || ($requestSupType > 0)) {
                echo Html::a('Добавить', [
                'company/create',
                'Company[type]' => $searchModel->type,
                'Company[sub_type]' => $requestSupType,
                'Company[status]' => Company::STATUS_ARCHIVE
            ], ['class' => 'btn btn-danger btn-sm']);
            }

            ?>
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
                    /*'groupFooter' => function ($data) {
                        return [
                            'content' => [
                                2 => "Итого " . $GLOBALS["typeName"],
                                3 => GridView::F_COUNT
                            ],
                            'contentFormats' => [
                                2 => ['format' => 'text'],
                                3 => ['format' => 'number']
                            ],
                            'contentOptions' => [
                                2 => ['style' => 'color:#8e8366;'],
                                3 => ['style' => 'color:#8e8366;'],
                            ],
                        ];
                    },*/
                ],
                [
                    'header' => 'Организация',
                    'attribute' => 'name',
                    'pageSummary' => 'Всего',
                    'contentOptions' => function ($data) {
                        return ($data->status == Company::STATUS_ACTIVE) ? ['style' => 'font-weight: bold'] : [];
                    },
                ],
                [
                    'attribute' => 'fullAddress',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_COUNT,
                    'content' => function ($data) {
                        return ($data->fullAddress) ? $data->fullAddress : 'не задан';
                    }
                ],
                [
                    'attribute' => 'email',
                    'content' => function ($data) {

                        if(isset($data->info->email)) {
                            if($data->info->email) {
                                return $data->info->email;
                            } else {
                                return 'не задан';
                            }
                        } else {
                            return 'не задан';
                        }

                    },
                    'filter' => true,
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/state', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>