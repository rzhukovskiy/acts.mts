<?php
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $model Company
 */

$replaceTender = Company::find()->innerJoin('tender_hystory', 'tender_hystory.company_id = company.id')->where(['company.id' => $model->id])->exists();

$items = [
    [
        'label' => Company::$listType[$model->type]['ru'],
        'url'   => [Company::$listStatus[$model->status]['en'], 'type' => $model->type],
    ],
    [
        'label'  => 'Процесс',
        'url'    => ['company/update', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'update',
    ],
    [
        'label'  => 'Статус клиента',
        'url'    => ['company/state', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'state',
    ],
    [
        'label'  => 'Инфо',
        'url'    => ['company/info', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'info',
    ],
    [
        'label'  => 'Сотрудники',
        'url'    => ['company/member', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'member',
    ],
    [
        'label'  => 'Данные заявки',
        'url'    => ['company/attribute', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'attribute',
    ]
];
if ($model->type == Company::TYPE_OWNER) {
    $items[] = [
        'label' => 'Водители',
        'url' => ['company/driver', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'driver' || \Yii::$app->controller->action->id == 'undriver',
    ];
}
$items[] = [
    'label' => 'Цены и ТС',
    'url' => ['company/price', 'id' => $model->id],
    'active' => \Yii::$app->controller->action->id == 'price',
];

if ($replaceTender) {
    $items[] = [
        'label' => 'Тендеры',
        'url' => ['company/tenders', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'tenders',
    ];
}

if($model->status == Company::STATUS_TENDER && $replaceTender) {
    $items = [
        [
            'label' => Company::$listType[$model->type]['ru'],
            'url'   => [Company::$listStatus[$model->status]['en'], 'type' => $model->type],
        ],
        [
            'label'  => 'Инфо',
            'url'    => ['company/info', 'id' => $model->id],
            'active' => \Yii::$app->controller->action->id == 'info',
        ],
        [
            'label'  => 'Процесс',
            'url'    => ['company/update', 'id' => $model->id],
            'active' => \Yii::$app->controller->action->id == 'update',
        ],
        [
            'label' => 'Тендеры',
            'url' => ['company/tenders', 'id' => $model->id],
            'active' => \Yii::$app->controller->action->id == 'tenders',
        ]
    ];
}

echo Tabs::widget([
    'items' => $items,
]);