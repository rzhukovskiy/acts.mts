<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $columns array
 * @var $is_locked bool
 */

use common\models\Company;
use common\models\User;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\web\View;
use yii\helpers\Url;

$actionLinkGetComments = Url::to('@web/load/getcomments');
$period = isset(Yii::$app->request->get('ActSearch')['period']) ? Yii::$app->request->get('ActSearch')['period'] : date("n-Y");
$type = Yii::$app->request->get('type');
// Выделение номера акта

$css = "#previewStatus {
background:#fff;
padding:12px;
position:fixed;
font-size:14px;
z-index:50;
border-radius:3px;
border:1px solid #069;
}

.showStatus:hover {
cursor:pointer;
}
";
$this->registerCss($css);

//Скрытие фильтров
$script = <<< JS
    $('.show-search').click(function(){
        $('#act-grid-filters').toggle();
    });

// Проценты закрытых и открытых
getPercentClosed();

// Изменение кнопки пересчет процентов
$('tr[data-key]').bind("DOMSubtreeModified",function(){
    getPercentClosed();
});

// Проценты закрытых и открытых
function getPercentClosed() {
var allNum = $('tr[data-key]').length;
$('.numAll').html('Всего: <b>' + allNum + '</b>');

var textClose = $('.numClose');
var textOpen = $('.numOpen');

var numClose = 0;
var numOpen = 0;

$('td[data-col-seq=CloseButt] a').each(function () {
    if($(this).text() == 'Закрыт') {
        numClose++;
    } else {
        numOpen++;
    }
});

var numClosePer = 0;

if (numClose == 0) {
    numClosePer = 0;
} else if (numClose == allNum) {
    numClosePer = 100;
} else {
    
var allNumPer = 0;
allNumPer = allNum / 100;
    
numClosePer = numClose / allNumPer;

if(("" + numClosePer.toFixed(2)).split(".")[1] > 0) {
numClosePer = numClosePer.toFixed(2);
}

}
var numOpenPer = 0;

if (numOpen == 0) {
    numOpenPer = 0;
} else if (numOpen == allNum) {
    numOpenPer = 100;
} else {
    
    numOpenPer = 100 - numClosePer;
    
    if(("" + numOpenPer.toFixed(2)).split(".")[1] > 0) {
        numOpenPer = numOpenPer.toFixed(2);
    }

}

textClose.html('Закрыто: <b>' + numClose + ' (' + numClosePer + '%)</b> загрузок');
textOpen.html('Открыто: <b>' + numOpen + ' (' + numOpenPer + '%)</b> загрузок');
}

// При наведении на название показывается статус актов
var margTop = 0;
var margLeft = 0;
var openWindowComm = false;

var arrRessComm = [];

var showStatusVar = $(".showStatus");
    showStatusVar.hover(function() {
        
        if(openWindowComm == false) {
        
            if($("#previewStatus")) {
                $("#previewStatus").remove(); 
            }
            
            openWindowComm = true;
            
            var companyName = $(this).text();
            
                if($(this).parent().data('id') > 0) {
        
                var idKey = $(this).parent().data('id');
                    console.log(idKey);
                if(arrRessComm[idKey]) {
                    
            this.t = this.title;
            this.title = "";
            var c = (this.t != "") ? "<br/>" + this.t : "";
            $("body").append("<p id='previewStatus'><b>" + companyName + "</b></p>");

            margTop = window.event.clientY - 20;
            margLeft = window.event.clientX + document.body.scrollLeft + 25;
            
            $("#previewStatus").css("top", margTop + "px")
            .css("left", margLeft + "px")
            .fadeIn("fast");
                    
                if($("#previewStatus")) {
                $("#previewStatus").html(arrRessComm[idKey]);
                }
                openWindowComm = false;
                } else {
                
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + idKey + '&period=' + '$period'  + '&type=' + '$type',
                url  : '$actionLinkGetComments',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                arrRessComm[idKey] = "<b>" + companyName + "</b><br />" + response.comment;
                
                if($("#previewStatus")) {
                $("#previewStatus").html(arrRessComm[idKey]);
                }
                openWindowComm = false;
                } else {
                // Неудачно
                openWindowComm = false;
                }
                
                }
                });
                
                }
        
            this.t = this.title;
            this.title = "";
            var c = (this.t != "") ? "<br/>" + this.t : "";
            $("body").append("<p id='previewStatus'><b>" + companyName + "</b></p>");

            margTop = window.event.clientY - 20;
            margLeft = window.event.clientX + document.body.scrollLeft + 25;
            
            $("#previewStatus").css("top", margTop + "px")
            .css("left", margLeft + "px")
            .fadeIn("fast");
                
                } else {
                    openWindowComm = false;
                }
            
            }
            
        },
        function() {
        if(openWindowComm == false) {
            $("#previewStatus").remove();
            margTop = 0;
            margLeft = 0;
            }
        });
    
    showStatusVar.mousemove(function(e) {
        margTop = window.event.clientY - 20;
        margLeft = window.event.clientX + document.body.scrollLeft + 25;
        $("#previewStatus")
            .css("top", margTop + "px")
            .css("left", margLeft + "px");
    });
// При наведении на название показывается статус актов
JS;
$this->registerJs($script, View::POS_READY);
//Выбор периода
$filters = 'Период: ' . DatePicker::widget([
        'model'         => $searchModel,
        'attribute'     => 'period',
        'type'          => DatePicker::TYPE_INPUT,
        'language'      => 'ru',
        'pluginOptions' => [
            'autoclose'       => true,
            'changeMonth'     => true,
            'changeYear'      => true,
            'showButtonPanel' => true,
            'format'          => 'm-yyyy',
            'maxViewMode'     => 2,
            'minViewMode'     => 1,
        ],
        'options'       => [
            'class' => 'form-control ext-filter',
        ]
    ]);

if ($role != User::ROLE_ADMIN && !empty(Yii::$app->user->identity->company->children)) {
    $filters .= ' Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter']);
}
if ($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) {
    $filters .= Html::a('Наклейки', array_merge(['load/stickers'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm', 'target' => '_blank']);
    $filters .= '<span class="numClose" style="margin-left: 5px; font-weight: normal; color: #2d6f31;"></span>';
    $filters .= '<span class="numOpen" style="margin-left: 15px; font-weight: normal; color: #8e3532;"></span>';
    $filters .= '<span class="numAll" style="margin-left: 15px; font-weight: normal;"></span>';
}

echo GridView::widget([
    'id' => 'act-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => ($hideFilter || $role != User::ROLE_ADMIN) ? null : $searchModel,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Услуги',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'resizableColumns' => false,
    'hover' => false,
    'striped' => false,
    'export' => false,
    'showPageSummary' => true,
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
                    //'content' => '<button class="btn btn-primary show-search">Поиск</button>',
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