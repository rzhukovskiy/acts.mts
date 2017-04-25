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
    $filters .= ' Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter']);
}
if ($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) {
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
                    'content' => '<button class="btn btn-primary show-search">Поиск</button>',
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