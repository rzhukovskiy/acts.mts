<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $admin boolean
 */

use kartik\grid\GridView;
use common\models\Changes;
use common\models\Company;

$GLOBALS['authorMembers'] = $authorMembers;
$GLOBALS['arrTypes'] = $arrTypes;
$GLOBALS['serviceList'] = $serviceList;
$columns = [];

if(Yii::$app->controller->action->id == 'card') {
    $columns = [
        [
            'header' => '№',
            'class' => 'kartik\grid\SerialColumn',
            'contentOptions' => ['style' => 'max-width: 40px'],
        ],
        [
            'attribute' => 'old_value',
            'header' => 'Номер карты',
        ],
        [
            'attribute' => 'company_id',
            'header' => 'Старая компания',
            'value' => function ($data) {
                if (isset($data->company_id)) {
                    if ($data->company_id) {

                        if (isset($GLOBALS['arrTypes'][$data->company_id])) {
                            return $GLOBALS['arrTypes'][$data->company_id];
                        } else {
                            return '-';
                        }

                    }
                }
                return '-';
            },
        ],
        [
            'attribute' => 'new_value',
            'header' => 'Новая компания',
            'value' => function ($data) {
                if (isset($data->new_value)) {
                    if ($data->new_value) {

                        if (isset($GLOBALS['arrTypes'][$data->new_value])) {
                            return $GLOBALS['arrTypes'][$data->new_value];
                        } else {
                            return '-';
                        }

                    }
                }
                return '-';
            },
        ],
        [
            'attribute' => 'user_id',
            'value' => function ($data) {
                return isset($GLOBALS['authorMembers'][$data->user_id]) ? $GLOBALS['authorMembers'][$data->user_id] : '';
            },
        ],
        [
            'attribute' => 'status',
            'value' => function ($data) {
                if ($data->status == Changes::NEW_CARD) {
                    return 'Добавление';
                } else if ($data->status == Changes::MOVE_CARD) {
                    return 'Перенос';
                }
            },
        ],
        [
            'attribute' => 'date',
            'value' => function ($data) {
                return date('d-m-Y', $data->date);
            },
        ],
    ];
} else {
    $columns = [
        [
            'header' => '№',
            'class' => 'kartik\grid\SerialColumn',
            'contentOptions' => ['style' => 'max-width: 40px'],
        ],
        [
            'attribute' => 'company_id',
            'value' => function ($data) {
                if (isset($data->company_id)) {
                    if ($data->company_id) {
                        $company = Company::find()->where(['id' => $data->company_id])->select('name')->column();

                        if (isset($company[0])) {
                            return $company[0];
                        } else {
                            return '-';
                        }

                    }
                }
                return '';
            },
        ],
        [
            'attribute' => 'service_id',
            'value' => function ($data) {
                return isset($GLOBALS['serviceList'][$data->service_id]) ? $GLOBALS['serviceList'][$data->service_id] : '-';
            },
        ],
        [
            'attribute' => 'type_id',
            'value' => function ($data) {
                return isset($GLOBALS['arrTypes'][$data->type_id]) ? $GLOBALS['arrTypes'][$data->type_id] : '';
            },
        ],
        [
            'attribute' => 'old_value',
            'value' => function ($data) {
                if ($data->status == Changes::NEW_PRICE) {
                    return '-';
                } else {
                    return $data->old_value;
                }
            },
        ],
        [
            'attribute' => 'new_value',
            'value' => function ($data) {
                return $data->new_value;
            },
        ],
        [
            'attribute' => 'user_id',
            'value' => function ($data) {
                return isset($GLOBALS['authorMembers'][$data->user_id]) ? $GLOBALS['authorMembers'][$data->user_id] : '';
            },
        ],
        [
            'attribute' => 'status',
            'value' => function ($data) {
                if ($data->status == Changes::NEW_PRICE) {
                    return 'Добавление';
                } else {
                    return 'Изменение';
                }
            },
        ],
        [
            'attribute' => 'date',
            'value' => function ($data) {
                return date('d-m-Y', $data->date);
            },
        ],
    ];
}
$filters = '';

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
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
            'showPageSummary' => false,
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
