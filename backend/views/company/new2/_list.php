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

$script = <<< JS

// Подсчет кол.
window.onload=function(){
var companyTR = $('tbody tr');

    var oldTR = "";
    var arruserCount = 0;
    var userName = [];
    var userOld = '';
    var userNow = '';
    var resTables = '';
    var resUsers = "";
    var numColumns = 0;
    var numCount = 0;
    var i = 0;
    $(companyTR).each(function (id, value) {

        var thisId = $(this);

        if(!(thisId.find('td div').hasClass('empty'))) {

            if (thisId.attr('class') == "kv-grid-group-row") {

                if (i > 0) {
                    var trFooter = $('<tr>').addClass('kv-group-footer');

                    var footerTd0 = $('<td>').text("");
                    var footerTd1 = $('<td>').text("Итого").css('color', '#8e8366').css('font-weight', 'bold');
                    var footerTd2 = $('<td>').text(numCount).css('color', '#8e8366').css('font-weight', 'bold');

                    var footerTdmerge = $('<td>').text("").attr("colspan", (numColumns - 3));
                    trFooter.append(footerTd0);
                    trFooter.append(footerTd1);
                    trFooter.append(footerTd2);
                    trFooter.append(footerTdmerge);

                    oldTR.after(trFooter);

                    }
                    userNow = thisId.find($('td[data-even-css="kv-group-header"]')).text();
                    
                  if (i == 0) {
                      userName[userNow] = 0;
                  } else {
                      userName[userOld] = numCount;
                  }
                    
                    
                numCount = 0;
            } else if (thisId.attr('class') == "kv-page-summary warning") {
                
                if (i > 0) {
                    var trFooter = $('<tr>').addClass('kv-group-footer');

                    var footerTd0 = $('<td>').text("");
                    var footerTd1 = $('<td>').text("Итого").css('color', '#8e8366').css('font-weight', 'bold');
                    var footerTd2 = $('<td>').text(numCount).css('color', '#8e8366').css('font-weight', 'bold');

                    var footerTdmerge = $('<td>').text("").attr("colspan", (numColumns - 3));
                    trFooter.append(footerTd0);
                    trFooter.append(footerTd1);
                    trFooter.append(footerTd2);
                    trFooter.append(footerTdmerge);

                    oldTR.after(trFooter);
                    userName[userNow] = numCount;
                }
                } else if (thisId.attr('data-key') > 0) {

                numCount++;

                // Записываем колчество ячеек
                if (numColumns == 0) {
                    numColumns = thisId.find('td').length;
                }

            }
            userOld = userNow;
            oldTR = thisId;
            i++;

        }

    });

for (var key in userName) {
    if (userName.hasOwnProperty(key)) {  
        resUsers += '<tr style="background: #fff; font-weight: normal;"><td style="padding: 3px 5px 3px 5px">'+ key +'</td><td style="padding: 3px 5px 3px 5px">' + userName[key] + '</td></tr>';
    }
}
// Подсчет кол.

            resTables = '<table width="100%" border="1" bordercolor="#dddddd" style="margin: 15px 0px 15px 0px;">' +
             '<tr style="background: #428bca; color: #fff;">' +
              '<td colspan="3" style="padding: 3px 5px 3px 5px; font-weight: normal;" align="center">Количество заявок</td>' +
               '</tr>' +
                '<tr style="background: #fff; font-weight: normal;">' + resUsers + '</tr></table>';
           if(resUsers.length > 0) {
            $('.place_list').html(resTables);
            }
};

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

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

            // Карта
            echo Html::a('Карта', [
                'company/map',
                'status' => $searchModel->status,
                'type' => $searchModel->type,
            ], ['class' => 'btn btn-success btn-sm', 'style' => 'margin-right:15px;', 'target' => '_blank']);

            // Тип ТС
            echo Html::a('Грузовые', [
                'company/' . Yii::$app->controller->action->id,
                'type' => $searchModel->type,
                'CompanySearch[car_type]' => 0,
            ], ['class' => 'btn btn-warning btn-sm', 'style' => 'margin-right:15px;']);

            echo Html::a('Легковые', [
                'company/' . Yii::$app->controller->action->id,
                'type' => $searchModel->type,
                'CompanySearch[car_type]' => 1,
            ], ['class' => 'btn btn-warning btn-sm', 'style' => 'margin-right:15px;']);

            echo Html::a('Универсальные', [
                'company/' . Yii::$app->controller->action->id,
                'type' => $searchModel->type,
                'CompanySearch[car_type]' => 2,
            ], ['class' => 'btn btn-warning btn-sm', 'style' => 'margin-right:15px;']);

            echo Html::a('Сбросить фильтр', [
                'company/' . Yii::$app->controller->action->id,
                'type' => $searchModel->type,
            ], ['class' => 'btn btn-success btn-sm', 'style' => 'margin-right:15px;']);
            // Тип ТС

            if(($searchModel->type != 3) || ($requestSupType > 0)) {
                if($requestSupType > 0) {
                    echo Html::a('Добавить', [
                        'company/create',
                        'Company[type]' => $searchModel->type,
                        'sub' => $requestSupType,
                        'Company[sub_type]' => $requestSupType,
                        'Company[status]' => Company::STATUS_NEW2
                    ], ['class' => 'btn btn-danger btn-sm']);
                } else {
                    echo Html::a('Добавить', [
                        'company/create',
                        'Company[type]' => $searchModel->type,
                        'Company[sub_type]' => $requestSupType,
                        'Company[status]' => Company::STATUS_NEW2
                    ], ['class' => 'btn btn-danger btn-sm']);
                }
            }

            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $statTable = '<div class="place_list"></div>';

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
                            'content' => $statTable,
                            'options' => [
                                'colspan' => 7,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-filter'],
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
                    'template' => '{map}{update}',
                    'contentOptions' => ['style' => 'min-width: 70px'],
                    'buttons' => [
                        'map' => function ($url, $model, $key) {
                            return $model->fullAddress ? Html::a('<span class="glyphicon glyphicon-map-marker" style="font-size:17px;"></span>',
                                ['/company/map', 'id' => $model->id], ['target' => '_blank']) : '';
                        },
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