<?php
/**
 * Created by PhpStorm.
 * User: rzhukovskiy
 * Date: 07.09.2016
 * Time: 16:05
 */

namespace common\components;


use common\models\Act;
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
                'contentOptions' => function ($data) {
                    if ($data->hasError(Act::ERROR_LOST)) return ['class' => 'text-danger'];
                },
            ],
            'mark' => [
                'attribute' => 'mark_id',
                'filter' => Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
                'value' => function ($data) {
                    return isset($data->mark) ? $data->mark->name : 'error';
                },
            ],
            'car' => [
                'attribute' => 'car_number',
                'value' =>  function ($data) {
                    return $data->car_number . ($data->client->is_split ? " ($data->extra_car_number)" : '');
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
                'attribute' => 'card_number',
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
                    $showServiceName = implode('+', $services);

                    // заменяем внутри+снаружи на снаружи+внутри
                    if(mb_strpos($showServiceName, 'внутри+снаружи')  !==  false) {
                        $showServiceName = str_replace('внутри+снаружи', 'снаружи+внутри', $showServiceName);
                    }

                    return $showServiceName;
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

                    $showServiceName = implode('+', $services);

                    // заменяем внутри+снаружи на снаружи+внутри
                    if(mb_strpos($showServiceName, 'внутри+снаружи') !== false) {
                        $showServiceName = str_replace('внутри+снаружи', 'снаружи+внутри', $showServiceName);
                    }

                    return $showServiceName;
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
                    if ($data->check && $imageLink) {
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
                'buttons' => [
                    'delete' => function ($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', [
                            'delete',
                            'id' => $data->id,
                        ], [
                            'data-confirm' => "Вы уверены, что хотите удалить этот элемент?"
                        ]);
                    },
                ],
            ],
            'partnerButtons' => [
                'header' => '',
                'mergeHeader' => false,
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($url, $data, $key) {
                        return ($data->created_at > (time() - 3600 * 3)) ? Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['update', 'id' => $data->id, 'company' => 1]) : '';
                    },
                ],
                'width' => '40px',
            ],
            'viewButtons' => [
                'header' => '',
                'mergeHeader' => false,
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{view}',
                'width' => '40px',
                'buttons' => [
                    'view' => function ($url, $data, $key) use ($company){
                        if ($company) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id, 'company' => $company]);
                        } else {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id]);
                        }
                    },
                ],
            ],
        ];

        $assets = [
            User::ROLE_ADMIN => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'expense', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense', 'updateButtons'],
                    Service::TYPE_PARKING => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'clientService', 'income', 'city', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'income', 'updateButtons'],
                    Service::TYPE_PARKING => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                ]
            ],
            User::ROLE_WATCHER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'expense', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense', 'updateButtons'],
                    Service::TYPE_PARKING => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'clientService', 'income', 'city', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'income', 'updateButtons'],
                    Service::TYPE_PARKING => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                ]
//                [
//                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'clientService', 'expense', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense'],
//                ],
//                [
//                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'income', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'income'],
//                ]
            ],
            User::ROLE_MANAGER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'expense', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense', 'updateButtons'],
                    Service::TYPE_PARKING => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'updateButtons', 'viewButtons'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'clientService', 'income', 'city', 'check', 'updateButtons'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'income', 'updateButtons'],
                    Service::TYPE_PARKING => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'updateButtons', 'viewButtons'],
                ]
//                [
//                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'clientService', 'expense', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense'],
//                ],
//                [
//                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'income', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'income'],
//                ]
            ],
            User::ROLE_PARTNER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'expense', 'check', 'partnerButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons', 'partnerButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons', 'partnerButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense'],
                    Service::TYPE_PARKING => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons', 'partnerButtons'],
                ],
            ],
            User::ROLE_CLIENT => [
                [],
                [
                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'clientService', 'income', 'city', 'check'],
                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'income', 'buttons'],
                    Service::TYPE_PARKING => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'viewButtons'],
                ]
            ],
        ];

        if (!$hasChildren && $assets[$role][$company][$type][1] == 'clientParent') {
            unset($assets[$role][$company][$type][1]);
        }
        return array_intersect_key($columns, array_flip($assets[$role][$company][$type]));
    }
}