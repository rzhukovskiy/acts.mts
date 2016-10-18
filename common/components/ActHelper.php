<?php
/**
 * Created by PhpStorm.
 * User: rzhukovskiy
 * Date: 07.09.2016
 * Time: 16:05
 */

namespace common\components;


use common\models\Act;
use common\models\Card;
use common\models\Company;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use common\models\User;
use kartik\grid\GridView;
use yii\bootstrap\Html;

class ActHelper
{
    /**
     * @param $type int
     * @param $role string
     * @param $company bool
     * @param $hasChildren bool
     * @return array
     */
    public static function getColumnsByType($type, $role, $company = false, $hasChildren = false) {
        $columns = [
            'row' => [
                'header' => '№',
                'class' => 'kartik\grid\SerialColumn',
                'pageSummary' => 'Всего',
                'mergeHeader' => false,
                'width' => '30px',
                'vAlign' => GridView::ALIGN_TOP,
            ],
            'clientParent' => [
                'attribute' => 'parent_id',
                'value' => function ($data) {
                    return isset($data->client->parent) ? $data->client->parent->name : 'без филиалов';
                },
                'headerOptions' => ['class' => 'hidden'],
                'filterOptions' => ['class' => 'hidden'],
                'footerOptions' => ['class' => 'hidden'],
                'pageSummaryOptions' => ['class' => 'hidden'],
                'contentOptions' => function ($data) {
                    return isset($data->client->parent) ? [
                        'class' => 'grouped hidden',
                        'data-header' => $data->client->parent->name,
                        'data-footer' => 'Итого ' . $data->client->parent->name . ':',
                    ] : ['class' => 'hidden'];
                },
            ],
            'partnerParent' => [
                'attribute' => 'parent_id',
                'value' => function ($data) {
                    return isset($data->partner->parent) ? $data->partner->parent->name : 'без филиалов';
                },
                'headerOptions' => ['class' => 'hidden'],
                'filterOptions' => ['class' => 'hidden'],
                'footerOptions' => ['class' => 'hidden'],
                'pageSummaryOptions' => ['class' => 'hidden'],
                'contentOptions' => function ($data) {
                    return isset($data->partner->parent) ? [
                        'class' => 'grouped hidden',
                        'data-header' => $data->partner->parent->name,
                        'data-footer' => 'Итого ' . $data->partner->parent->name . ':',
                    ] : ['class' => 'hidden'];
                },
            ],
            'client' => [
                'attribute' => 'client_id',
                'value' => function ($data) {
                    return isset($data->client) ? $data->client->name . ' - ' . $data->client->address : 'error';
                },
                'headerOptions' => ['class' => 'hidden'],
                'filterOptions' => ['class' => 'hidden'],
                'footerOptions' => ['class' => 'hidden'],
                'pageSummaryOptions' => ['class' => 'hidden'],
                'contentOptions' => function ($data) {
                    return isset($data->client) ? [
                        'class' => 'grouped hidden',
                        'data-header' => $data->client->name . ' - ' . $data->client->address,
                        'data-footer' => 'Итого ' . $data->client->name . ':',
                        'data-parent' => 1,
                    ] : ['class' => 'hidden'];
                },
                'filter' => false,
            ],
            'partner' => [
                'attribute' => 'partner_id',
                'value' => function ($data) {
                    return isset($data->partner) ? $data->partner->name . ' - ' . $data->partner->address : 'error';
                },
                'headerOptions' => ['class' => 'hidden'],
                'filterOptions' => ['class' => 'hidden'],
                'footerOptions' => ['class' => 'hidden'],
                'pageSummaryOptions' => ['class' => 'hidden'],
                'contentOptions' => function ($data) {
                    return isset($data->partner) ? [
                        'class' => 'grouped hidden',
                        'data-header' => $data->partner->name . ' - ' . $data->partner->address,
                        'data-footer' => 'Итого ' . $data->partner->name . ':',
                        'data-parent' => 1,
                    ] : ['class' => 'hidden'];
                },
                'filter' => false,
            ],
            'day' => [
                'attribute' => 'day',
                'filter' => Act::getDayList(),
                'value' => function ($data) use ($role) {
                    return $role == User::ROLE_ADMIN ? date('j', $data->served_at) : date('d-m-Y', $data->served_at);
                },
                'width' => $role == User::ROLE_ADMIN ? '20px' : '100px',
            ],
            'mark' => [
                'attribute' => 'mark_id',
                'filter' => Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
                'value' => function ($data) {
                    return isset($data->mark) ? $data->mark->name : 'error';
                },
            ],
            'number' => [
                'attribute' => 'number',
                'value' =>  function ($data) {
                    return $data->number . ($data->client->is_split ? " ($data->extra_number)" : '');
                },
                'contentOptions' => function ($data) {
                    if ($data->hasError('car')) return ['class' => 'text-danger'];
                },
            ],
            'type' => [
                'attribute' => 'type_id',
                'filter' => Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
                'value' => function ($data) {
                    return isset($data->type) ? $data->type->name : 'error';
                },
            ],
            'card' => [
                'attribute' => 'card_id',
                'filter' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
                'value' => function ($data) {
                    return isset($data->card) ? $data->card->number : 'error';
                },
                'contentOptions' => function ($data) {
                    if ($data->hasError('card')) return ['style' => 'min-width:80px', 'class' => 'text-danger'];
                    return ['style' => 'min-width:80px'];
                },
                'width' => '80px',
            ],
            'clientService' => [
                'header' => 'Услуга',
                'value' => function ($data) {
                    /** @var \common\models\ActScope $scope */
                    $services = [];
                    foreach ($data->clientScopes as $scope) {
                        $services[] = $scope->description;
                    }
                    return implode('+', $services);
                },
                'width' => '140px',
            ],
            'partnerService' => [
                'header' => 'Услуга',
                'value' => function ($data) {
                    /** @var \common\models\ActScope $scope */
                    $services = [];
                    foreach ($data->partnerScopes as $scope) {
                        $services[] = $scope->description;
                    }
                    return implode('+', $services);
                },
                'width' => '140px',
            ],
            'income' => [
                'attribute' => 'income',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'contentOptions' => function ($data) {
                    $options['class'] = 'sum';
                    if ($data->hasError('income')) $options['class'] .= ' text-danger';
                    return $options;
                },
            ],
            'expense' => [
                'attribute' => 'expense',
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'contentOptions' => function ($data) {
                    $options['class'] = 'sum';
                    if ($data->hasError('expense')) $options['class'] .= ' text-danger';
                    return $options;
                },
            ],
            'city' => 'partner.address',
            'check' => [
                'attribute' => 'check',
                'value' => function ($data) {
                    $imageLink = $data->getImageLink();
                    if ($imageLink) {
                        return Html::a($data->check, $imageLink, ['class' => 'preview']);
                    }
                    return 'error';
                },
                'format' => 'raw',
                'contentOptions' => function ($data) {
                    if ($data->hasError('check')) return ['class' => 'text-danger'];
                },
                'width' => '60px',
            ],
            'updateButtons' => [
                'header' => '',
                'mergeHeader' => false,
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update}{delete}',
                'width' => '70px',
            ],
            'partnerButtons' => [
                'header' => '',
                'mergeHeader' => false,
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update}',
                'width' => '40px',
            ],
            'viewButtons' => [
                'header' => '',
                'mergeHeader' => false,
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{view}',
                'width' => '40px',
                'buttons' => [
                    'view' => function ($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id, 'company' => 1]);
                    },
                ],
            ],
        ];

        $assets = [
            User::ROLE_ADMIN => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'partnerService', 'expense', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'number', 'type', 'expense', 'updateButtons'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'clientService', 'income', 'city', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'income', 'updateButtons'],
                ]
            ],
            User::ROLE_WATCHER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'clientService', 'expense', 'check'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'number', 'type', 'expense'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'partnerService', 'income', 'check'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'income'],
                ]
            ],
            User::ROLE_PARTNER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'partnerService', 'expense', 'check', 'partnerButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'number', 'type', 'expense'],
                ],
            ],
            User::ROLE_CLIENT => [
                [],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'clientService', 'income', 'city', 'check'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'city', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'city', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'income', 'buttons'],
                ]
            ],
        ];

        if (!$hasChildren && $assets[$role][$company][$type][1] == 'clientParent') {
            unset($assets[$role][$company][$type][1]);
        }
        return array_intersect_key($columns, array_flip($assets[$role][$company][$type]));
    }

    /**
     * @param $act \common\models\Act
     * @return bool
     */
    public static function isNoCheck($act){
        return $act->service_type == Service::TYPE_WASH && !$act->getImageLink();
    }


}