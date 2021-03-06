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

//Скрытие фильтров
$script = <<< JS
    $('.show-search').click(function(){
        $('#act-grid-filters').toggle();
    });
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

    $filters .= ' Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->where(['id' => $arrParParIds])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter']);
}
if ($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) {

    // Скрипт скрытия актов в филиалы
    $script = <<< JS
    
    var actTR = $('tbody tr');
    var actTitleCompany = $('tr[class="kv-group-header child"] td');
    actTitleCompany.css("cursor", "pointer");
    
    var arrayStatusHide = [];
    var arrayChildAct = [];
    var closeAllButtStatus = 1;
    
    var checkLoadArrays = false;
    
    // Скрываем/отображаем акты
    function hideActs(name) {
        
        if(arrayStatusHide[name] == 1) {
            
            arrayChildAct[name].forEach(function (value) {
                $(value).show();
            });
            

            arrayStatusHide[name] = 0
        } else {
            
            arrayChildAct[name].forEach(function (value) {
                $(value).hide();
            });
            
            arrayStatusHide[name] = 1
        }
        
    }
    
    // Получаем название филиалов для индекса и задаем по умолчанию скрытие
    $(actTitleCompany).each(function (id, value) {
        arrayStatusHide[$(this).text()] = 1;
    });
    
    // Получаем список актов для каждой компании
    
    var old_company = "";
    $(actTR).each(function (id, value) {
        
        var thisId = $(this);
        
        if((thisId.attr('class') == "kv-group-header child") || (thisId.attr('data-key') > 0)) {
        //console.log(thisId.attr('class'));
            
        if(thisId.attr('class') == "kv-group-header child") {
            old_company = $(thisId).children('td').text();
            
            arrayChildAct[old_company] = [];
            
        } else {
            $(thisId).hide();
            arrayChildAct[old_company].push(thisId);
        }
        
        //console.log(old_company);
        
        }
        
    });
    checkLoadArrays = true;
   
    // Нажимаем кнопку скрыть/отобразить акты
    actTitleCompany.on('click', function(){
        if(checkLoadArrays == true) {
        hideActs($(this).text());
        }
    });
    
    // Нажимаем кнопку скрыть/отобразить все акты
    $('.openActs').on('click', function(){
        if(checkLoadArrays == true) {
            
            if(closeAllButtStatus == 1) {
                
                $(actTitleCompany).each(function (id, value) {
                    arrayStatusHide[$(this).text()] = 0;
                    
                    arrayChildAct[$(this).text()].forEach(function (value) {
                        $(value).show();
                    });
                    
                });
                
                closeAllButtStatus = 0;
                $('.openActs').text('Закрыть все акты');
                
            } else {
                
                $(actTitleCompany).each(function (id, value) {
                    arrayStatusHide[$(this).text()] = 1;
                    
                    arrayChildAct[$(this).text()].forEach(function (value) {
                        $(value).hide();
                    });
                    
                });
                
                closeAllButtStatus = 1;
                $('.openActs').text('Открыть все акты');
                
            }
            
        }
    });

JS;
$this->registerJs($script, View::POS_READY);
    // Скрипт скрытия актов в филиалы

    $type_linking = $company ? $company : Yii::$app->request->get('type');

    $filters .= ' Выбор сотрудника: ' . Html::activeDropDownList($searchModel, 'user_id', \common\models\DepartmentLinking::find()->where(['department_linking.type' => $type_linking])
            ->innerJoin('user', 'user.id = department_linking.user_id')
            ->select(['user.username', 'department_linking.user_id as id'])->indexBy('id')->column(), ['prompt' => 'Все сотрудники','class' => 'form-control ext-filter', 'style' => 'width: 200px;']);

    $filters .= Html::a('Выгрузить', array_merge(['act/export'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm']);
    $filters .= Html::a('Пересчитать', array_merge(['act/fix'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm']);
}
if ($role == User::ROLE_ADMIN) {
    if ($is_locked) {
        $filters .= Html::a('Открыть загрузку', array_merge(['act/unlock'], Yii::$app->getRequest()->get()), [
            'class' => 'pull-right btn btn-warning btn-sm',
            'title' => Yii::t('yii', 'Close'),
            'onclick' => "button = $(this); $.ajax({
                type     :'GET',
                cache    : false,
                url  : $(this).attr('href'),
                success  : function(response) {
                    button.text(response);
                    button.attr('href', '#');
                }
                });
                return false;",
            ]);
    } else {
        $filters .= Html::a('Закрыть загрузку', array_merge(['act/lock'], Yii::$app->getRequest()->get()), [
            'class' => 'pull-right btn btn-warning btn-sm',
            'title' => Yii::t('yii', 'Close'),
            'onclick' => "button = $(this); $.ajax({
                type     :'GET',
                cache    : false,
                url  : $(this).attr('href'),
                success  : function(response) {
                    button.text(response);
                    button.attr('href', '#');
                }
                });
                return false;",
        ]);
    }
}

echo GridView::widget([
    'id' => 'act-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => ($hideFilter/* || $role != User::ROLE_ADMIN*/) ? null : $searchModel,
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
                    'content' => '<button class="btn btn-primary show-search">Поиск</button>' . (($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) ?'<button class="pull-right btn btn-warning openActs" style="padding: 6px 8px; margin-top: 2px; border:1px solid #c18431;">Открыть все акты</button>' : ''),
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