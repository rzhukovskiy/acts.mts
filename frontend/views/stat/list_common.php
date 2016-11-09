<?php

use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/**
 * @var $this yii\web\View
 * @var $group string
 * @var $type integer
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $totalServe float
 * @var $totalProfit float
 * @var $totalExpense float
 * @var $title string
 */

CanvasJsAsset::register($this);
$this->title = $title;
$filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel,
        'client_id',
        Company::dataDropDownList(Company::TYPE_OWNER),
        ['prompt' => 'все', 'class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);

/**
 * Конец виджета
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => ['stat/list-common'],
            'method' => 'get',
            'id'     => 'contact-form',
        ]); ?>
        <div class="kv-grid-group-filter period-select graph-select">
            <?= $filters ?>
            <?=
            \frontend\widgets\datePeriod\DatePeriodWidget::widget([
                'model'        => $searchModel,
                'dateFromAttr' => 'dateFrom',
                'dateToAttr'   => 'dateTo',
            ]);
            ?>
        </div>
        <?php ActiveForm::end(); ?>
        <hr>
        <div id="chart_div" style="width:100%;height:500px;">
            <? if (empty($data)) {
                echo "Нет данных за выбранный период";
            } ?>

        </div>

        <?php
        // TODO: refactor it, plz, move collecting data into controller
        $js = "
        CanvasJS.addCultureInfo('ru',
        {
            shortMonths: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
        });
        
           var chart = new CanvasJS.Chart('chart_div',{
            zoomEnabled: false,
            animationEnabled: true,
            title:{
                text: '$title',
                fontSize:20
            },
            axisX:{
				gridColor: 'Silver',
				tickColor: 'silver',
				valueFormatString: '" . $xFormat['valueFormatString'] . "',
				interval:" . $xFormat['interval'] . ",
                intervalType: '" . $xFormat['intervalType'] . "'
			},
            axisY2:{
                    valueFormatString:'0',
                    maximum: $maximum,
                    interval: $step,
                    interlacedColor: '#F5F5F5',
                    gridColor: '#D7D7D7',
                    tickColor: '#D7D7D7'
            },
            theme: 'theme2',
                toolTip:{
                shared: true
            },
            culture:'ru',
            legend:{
                verticalAlign: 'bottom',
                horizontalAlign: 'center',
                fontSize: 15,
                fontFamily: 'Lucida Sans Unicode'
            },
            data: $data,
			legend: {
                cursor:'pointer',
                itemclick : function(e) {
                    if (typeof(e.dataSeries.visible) === 'undefined' || e.dataSeries.visible) {
                        e.dataSeries.visible = false;
                    }
                    else {
                        e.dataSeries.visible = true;
                    }
                chart.render();
                }
          }
        });
           chart.render();";
        if (!empty($data)) {
            $this->registerJs($js);
        }
        ?>
    </div>
</div>

<script>

</script>