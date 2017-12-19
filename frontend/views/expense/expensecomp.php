<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Tabs;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Url;

$script = <<< JS

$('.field-expense-type').css("display", "none");
$('.field-expense-expense_company').css("display", "none");

// формат числа
window.onload=function(){
    if ($model->type == 1) {
  var formatSum2 = $('td[data-col-seq="2"]');
  $(formatSum2).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum3 = $('td[data-col-seq="3"]');
  $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum4 = $('td[data-col-seq="4"]');
  $(formatSum4).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum5 = $('td[data-col-seq="5"]');
  $(formatSum5).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
    var formatSum6 = $('td[data-col-seq="6"]');
  $(formatSum6).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
    var formatSum7 = $('td[data-col-seq="7"]');
  $(formatSum7).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
      var formatSum8 = $('td[data-col-seq="8"]');
  $(formatSum8).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum2 = $('.kv-page-summary-container td:eq(2)');
  $(formatSum2).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum3 = $('.kv-page-summary-container td:eq(3)');
  $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum4 = $('.kv-page-summary-container td:eq(4)');
  $(formatSum4).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum5 = $('.kv-page-summary-container td:eq(5)');
  $(formatSum5).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
    var formatSum6 = $('.kv-page-summary-container td:eq(6)');
  $(formatSum6).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
    var formatSum7 = $('.kv-page-summary-container td:eq(7)');
  $(formatSum7).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
      var formatSum8 = $('.kv-page-summary-container td:eq(8)');
  $(formatSum8).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  } else {
        var formatSum3 = $('td[data-col-seq="3"]');
  $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
   var formatSum3 = $('.kv-page-summary-container td:eq(3)');
  $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  }
};
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$this->title = 'Редактирование ' . $model->name

?>

<?php echo Tabs::widget([
    'items' => [
        ['label' => 'Полный список', 'url' => ['addexpensecomp', 'type' => $model->type], 'active' => Yii::$app->controller->action->id == 'addexpensecomp'],
        ['label' => 'Редактирование', 'url' => ['expensecomp', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'expensecomp'],
    ],
]);
?>


<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->isNewRecord ? 'Добавление ' : 'Редактирование ' . $model->name ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['expense/addexpensecomp', 'type' => $model->type] : ['expense/updateexpense', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'name') ?>


        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Добавить затраты ' . $model->name?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $newmodel->isNewRecord ? ['expense/addexpense', 'id' => $model->id] : ['expense/updateexpense', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>

        <?php if ($model->type !== 1) { echo $form->field($newmodel, 'description')->input('text', ['class' => 'form-control', 'placeholder' => 'Наименование']); } ?>
        <?= $form->field($newmodel, 'sum')->input('text', ['class' => 'form-control', 'placeholder' => 'Сумма']) ?>
        <?= $form->field($newmodel, 'date')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Выберите дату'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>
        <?= $form->field($newmodel, 'type')->hiddenInput(['value' => $model->type])->label(false) ?>
        <?= $form->field($newmodel, 'expense_company')->hiddenInput(['value' => $model->id])->label(false) ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        Список расходов
    </div>
    <div class="panel-body">
        <?php
        if ($model->type == 1) {
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'showPageSummary' => true,
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'vAlign'=>'middle',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'date',
                    'vAlign'=>'middle',
                    'pageSummary' => 'Всего',
                    'value' => function ($data) {

                        if ($data->date) {
                            return date('d.m.Y', $data->date);
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
                            return $data->sum * 13/100;
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
                            return $data->sum * 22/100;
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
                            return $data->sum * 5.1/100;
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
                            return $data->sum * 2.9/100;
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
                            return $data->sum * 0.5/100;
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
                            return $data->sum + $data->sum * 43.5/100;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'header' => 'Действие',
                    'vAlign'=>'middle',
                    'template' => '{update}{delete}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/expense/fullexpense', 'id' => $model->id]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/expense/delete', 'id' => $model->id],
                                ['data-confirm' => "Вы уверены, что хотите удалить этот элемент?"]);
                        },
                    ],
                ],
            ],
        ]);
        } else {
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'summary' => false,
                'showPageSummary' => true,
                'emptyText' => '',
                'layout' => '{items}',
                'columns' => [
                    [
                        'header' => '№',
                        'vAlign'=>'middle',
                        'class' => 'kartik\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'description',
                        'vAlign'=>'middle',
                        'pageSummary' => 'Всего',
                        'value' => function ($data) {

                            if ($data->description) {
                                return $data->description;
                            } else {
                                return '-';
                            }

                        },
                    ],
                    [
                        'attribute' => 'date',
                        'vAlign'=>'middle',
                        'value' => function ($data) {
                            if ($data->date) {
                                return date('d.m.Y', $data->date);
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
                        'template' => '{update}{delete}',
                        'contentOptions' => ['style' => 'min-width: 60px'],
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                    ['/expense/fullexpense', 'id' => $model->id]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/expense/delete', 'id' => $model->id],
                                    ['data-confirm' => "Вы уверены, что хотите удалить этот элемент?"]);
                            },
                        ],
                    ],
                ],
            ]);
        }
        ?>
    </div>
</div>


