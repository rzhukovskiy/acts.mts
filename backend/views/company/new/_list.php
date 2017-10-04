<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $userData array
 */
use common\models\Company;
use common\models\User;
use common\models\DepartmentCompany;
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

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
            <?php

            if(($searchModel->type != 3) || ($requestSupType > 0)) {
                if($requestSupType > 0) {
                    echo Html::a('Добавить', [
                        'company/create',
                        'Company[type]' => $searchModel->type,
                        'sub' => $requestSupType,
                        'Company[sub_type]' => $requestSupType,
                        'Company[status]' => Company::STATUS_NEW
                    ], ['class' => 'btn btn-danger btn-sm']);
                } else {
                    echo Html::a('Добавить', [
                        'company/create',
                        'Company[type]' => $searchModel->type,
                        'Company[sub_type]' => $requestSupType,
                        'Company[status]' => Company::STATUS_NEW
                    ], ['class' => 'btn btn-danger btn-sm']);
                }
            }

            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
//        if ($admin) {
//            echo $this->render('_selector', [
//                'type' => $type,
//                'userData' => $userData,
//                'searchModel' => $searchModel,
//            ]);
//        }

        $filters = 'Выбор сотрудника: ' . Html::activeDropDownList($searchModel, 'dep_user_id', DepartmentCompany::find()->andWhere(['department_company.remove_date' => null])->where(['!=', 'department_company.company_id', 0])
                ->innerJoin('user', 'user.id = department_company.user_id')
                ->innerJoin('company', 'company.id = department_company.company_id')
                ->andWhere(['company.type' => $type])
                ->andWhere(['company.status' => 1])
                ->select(['user.username', 'department_company.user_id AS dep_user_id'])->indexBy('dep_user_id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'showPageSummary' => true,
            'emptyText' => '',
            'filterSelector' => '.ext-filter',
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters,
                            'options' => ['colspan' => 7, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
                        ],
                    ],
                    'options' => ['class' => 'filters extend-header'],
                ],
                [
                    'columns' => [
                        [
                            'content' => '&nbsp',
                            'options' => [
                                'colspan' => 7,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],
            'rowOptions' => function ($model) {
                // Выделяем цветом для каких типов

                if($model->car_type == 0) {
                    // грузовые оставляем как есть
                    return '';
                } else if($model->car_type == 1) {
                    return ['style' => 'background: #dff1d8;'];
                } else if($model->car_type == 2) {
                    return ['style' => 'background: #f9f5e3;'];
                } else {
                    return '';
                }

            },
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'depart_user_name',
                    'group' => true,
                    'groupedRow' => true,
                    'groupOddCssClass' => 'kv-group-header',
                    'groupEvenCssClass' => 'kv-group-header',
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
                    'header' => 'Связь',
                    'attribute' => 'offer.communication_at',
                    'value' => function($model) {
                        return !empty($model->offer->communication_at) ? date('d-m-Y H:i', $model->offer->communication_at) : 'Не указана';
                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 70px'],
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