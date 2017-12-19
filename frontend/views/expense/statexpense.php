<?php

use common\models\ExpenseCompany;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\Expense;

/**
 * @var $this \yii\web\View
 * @var $listType array[]
 */

$script = <<< JS

// формат числа
window.onload=function(){
    if ($searchModel->type == 1 ) {
  var formatSum2 = $('td[data-col-seq="2"]');
  $(formatSum2).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum3 = $('td[data-col-seq="3"]');
  $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum4 = $('td[data-col-seq="4"]');
  $(formatSum4).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum5 = $('td[data-col-seq="5"]');
  $(formatSum5).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
    var formatSum6 = $('td[data-col-seq="6"]');
  $(formatSum6).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
    var formatSum7 = $('td[data-col-seq="7"]');
  $(formatSum7).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
      var formatSum8 = $('td[data-col-seq="8"]');
  $(formatSum8).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
        });
  
      var formatSum2a = $('.kv-page-summary-container td:eq(2)');
      if (formatSum3.length > 0) {
      $(formatSum2a).each(function (id, value) {
           var thisId = $(this);
           thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
            });
      }
  
       var formatSum3a = $('.kv-page-summary-container td:eq(3)');
       if (formatSum3.length > 0) {
       $(formatSum3a).each(function (id, value) {
           var thisId = $(this);
           thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
            });
        }
  
  var formatSum4a = $('.kv-page-summary-container td:eq(4)');
       if (formatSum3.length > 0) {
  $(formatSum4a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
  
  var formatSum5a = $('.kv-page-summary-container td:eq(5)');
       if (formatSum3.length > 0) {
  $(formatSum5a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
  
    var formatSum6a = $('.kv-page-summary-container td:eq(6)');
       if (formatSum3.length > 0) {
  $(formatSum6a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
  
    var formatSum7a = $('.kv-page-summary-container td:eq(7)');
       if (formatSum3.length > 0) {
  $(formatSum7a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
  
      var formatSum8a = $('.kv-page-summary-container td:eq(8)');
       if (formatSum3.length > 0) {
  $(formatSum8a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(parseFloat(thisId.text()).toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
  } else {
        var formatSum3 = $('td[data-col-seq="3"]');
  $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
   var formatSum3a = $('.kv-page-summary-container td:eq(3)');
  $(formatSum3a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
};
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$this->title = ExpenseCompany::$listType[$searchModel->type]['ru'];

/**
 * Виджет выбора диапазона дат
 */
$halfs = [
    '1е полугодие',
    '2е полугодие'
];
$quarters = [
    '1й квартал',
    '2й квартал',
    '3й квартал',
    '4й квартал',
];
$months = [
    'январь',
    'февраль',
    'март',
    'апрель',
    'май',
    'июнь',
    'июль',
    'август',
    'сентябрь',
    'октябрь',
    'ноябрь',
    'декабрь',
];

$halfs = [
    '1е полугодие',
    '2е полугодие'
];
$quarters = [
    '1й квартал',
    '2й квартал',
    '3й квартал',
    '4й квартал',
];
$months = [
    'январь',
    'февраль',
    'март',
    'апрель',
    'май',
    'июнь',
    'июль',
    'август',
    'сентябрь',
    'октябрь',
    'ноябрь',
    'декабрь',
];

$ts1 = strtotime($searchModel->dateFrom);
$ts2 = strtotime($searchModel->dateTo);

$year1 = date('Y', $ts1);
$year2 = date('Y', $ts2);

$month1 = date('m', $ts1);
$month2 = date('m', $ts2);

$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
switch ($diff) {
    case 1:
        $period = 1;
        break;
    case 3:
        $period = 2;
        break;
    case 6:
        $period = 3;
        break;
    case 12:
        $period = 4;
        break;
    default:
        $period = 0;
}
$rangeYear = range(date('Y') - 10, date('Y'));
$currentYear = isset($searchModel->dateFrom)
    ? date('Y', strtotime($searchModel->dateFrom))
    : date('Y');

$currentMonth = isset($searchModel->dateFrom)
    ? date('n', strtotime($searchModel->dateFrom))
    : date('n');
$currentMonth--;

$filters = '';
$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, Expense::$periodList, [
    'class' =>'select-period form-control',
    'style' => 'margin-right: 10px;'
]);
$periodForm .= Html::dropDownList('month', $currentMonth, $months, [
    'id' => 'month',
    'class' => 'autoinput form-control',
    'style' => $diff == 1 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('half', $currentMonth < 5 ? 0 : 1, $halfs, [
    'id' => 'half',
    'class' => 'autoinput form-control',
    'style' => $diff == 6 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('quarter', floor($currentMonth / 3), $quarters, [
    'id' => 'quarter',
    'class' => 'autoinput form-control',
    'style' => $diff == 3 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('year', array_search($currentYear, $rangeYear), range(date('Y') - 10, date('Y')), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => $diff && $diff <= 12 ? '' : 'display:none'
]);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

$filters = Html::activeDropDownList($searchModel, 'expense_company', ExpenseCompany::find()->where(['expense_company.type' => $searchModel->type])->select(['expense_company.name'])->groupBy('`name`')->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);

$filters .= ' Выбор периода: ' . $periodForm;

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
foreach ($listType as $type_id => $typeData) {

    $items[] = [
        'label' => ExpenseCompany::$listType[$type_id]['ru'],
        'url' => ["/expense/$action", 'type' => $type_id],
        'active' => Yii::$app->controller->id == 'expense' && $requestType == $type_id,
    ];
}

$items[] = [
    'label' => 'Общее',
    'url' => ["/expense/stattotal"],
    'active' => Yii::$app->controller->id == 'expense' && Yii::$app->controller->action->id == 'stattotal',
];

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);
if ($searchModel->type == 1) {
    $columns = [
        [
            'header' => '№',
            'vAlign' => 'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'header' => 'ФИО',
            'vAlign' => 'middle',
            'pageSummary' => 'Всего',
            'value' => function ($data) {

                if ($data->expensecompany->name) {
                    return $data->expensecompany->name;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'sum',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'ndfl',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum/0.87 * 0.13;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'pfr',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum/0.87 * 0.22;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'foms',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum/0.87 * 0.051;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'fss',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum/0.87 * 0.029;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'fssns',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum/0.87 * 0.005;
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'Итого',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum + $data->sum/0.87 * 0.435;
                } else {
                    return '-';
                }

            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign' => 'middle',
            'template' => '{update}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $searchModel, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/expense/fullexpense', 'id' => $searchModel->id]);
                },
            ],
        ],
    ];
} else {
    $columns =  [
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'header' => 'Название организации',
            'vAlign'=>'middle',
            'pageSummary' => 'Всего',
            'value' => function ($data) {

                if ($data->expensecompany->name) {
                    return $data->expensecompany->name;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'description',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->description) {
                    return $data->description;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'sum',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {

                if ($data->sum) {
                    return $data->sum;
                } else {
                    return '-';
                }

            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{update}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'update' => function ($url, $searchModel, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/expense/fullexpense', 'id' => $searchModel->id]);
                },
            ],
        ],
    ];
}

?>

<div class="panel panel-primary">
    <div class="panel-heading">
Список расходов
</div>
    <div class="panel-body">
        <?php

               echo GridView::widget([
                'dataProvider' => $dataProvider,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'summary' => false,
                'resizableColumns' => false,
                'showPageSummary' => true,
                'emptyText' => '',
                'layout' => '{items}',
                   'filterSelector' => '.ext-filter',
                'beforeHeader' => [
                    [
                        'columns' => [
                            [
                                'content' => $filters,
                                'options' => [
                                    'style' => 'vertical-align: middle',
                                    'colspan' => count($columns),
                                    'class' => 'kv-grid-group-filter',
                                ],
                            ]
                        ],
                        'options' => ['class' => 'extend-header'],
                    ],
                    [
                        'columns' => [
                            [
                                'content' => '&nbsp',
                                'options' => [
                                    'colspan' => count($columns),
                                ]
                            ]
                        ],
                        'options' => ['class' => 'kv-group-header'],
                    ],
                ],
                'columns' => $columns,
                ]);
        ?>
</div>
</div>