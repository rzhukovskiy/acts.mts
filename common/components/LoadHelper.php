<?php
/**
 * Created by PhpStorm.
 * User: rzhukovskiy
 * Date: 07.09.2016
 * Time: 16:05
 */

namespace common\components;


use common\models\Act;
use common\models\Company;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use common\models\User;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;

class LoadHelper
{
    /**
     * @param $type int
     * @param $role string
     * @param $company bool
     * @param $hasChildren bool
     * @return array
     */
    public static function getColumnsByType($type, $role, $locked, $pediod, $company = false, $hasChildren = false) {

        $GLOBALS['locked'] = $locked;
        $GLOBALS['type'] = $type;
        $GLOBALS['pediod'] = $pediod;
        $GLOBALS['company'] = $company;

        $columns = [
            'row' => [
                'header' => '№',
                'class' => 'kartik\grid\SerialColumn',
                'pageSummary' => 'Всего',
                'mergeHeader' => false,
                'width' => '30px',
                'vAlign' => GridView::ALIGN_BOTTOM,
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
                    return ['class' => 'hidden'];
                },
                'filter' => false,
            ],
            'name' => [
                'header' => 'Название',
                'format' => 'raw',
                'value' => function ($data) {
                    if($GLOBALS['company'] == 1) {
                        return '<span class="showStatus">' . Company::find()->select(['name'])->where(['id' => $data->id])->column()[0] . '</span>';
                    } else {
                        return '<span class="showStatus">' . $data->partner->name . '</span>';
                    }
                },
                'contentOptions' =>function ($model, $key, $index, $column){
                    if($GLOBALS['company'] == 1) {
                        return ['data-company' => $model->id];
                    } else {
                        return ['data-company' => $model->partner_id];
                    }
                },
            ],
            'city' => [
                'attribute' => 'city',
                'value' => function ($data) {
                    if($GLOBALS['company'] == 1) {
                        return Company::find()->select(['address'])->where(['id' => $data->id])->column()[0];
                    } else {
                        return $data->partner->address;
                    }
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
            'CloseButt' => [
                'header' => 'Статус загрузки',
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{close}',
                'buttons' => [
                    'close' => function ($url, $data, $key) {

                        $LockedLisk = $GLOBALS['locked'];

                        if($GLOBALS['company'] == 1) {
                            $close_company = $data->id;
                        } else {
                            $close_company = $data->partner_id;
                        }

                        if(count($LockedLisk) > 0) {

                            $CloseAll = false;
                            $CloseCompany = false;

                            for ($c = 0; $c < count($LockedLisk); $c++) {
                                if ($LockedLisk[$c]["company_id"] == 0) {
                                    $CloseAll = true;
                                }
                                if ($LockedLisk[$c]["company_id"] == $close_company) {
                                    $CloseCompany = true;
                                }
                            }

                            if ((($CloseAll == true) && ($CloseCompany == false)) || (($CloseAll == false) && ($CloseCompany == true))) {
                               return Html::a('Закрыт', array_merge(['load/close'], ['type' => $GLOBALS['type'], 'company' => $close_company, 'period' => $GLOBALS['pediod']]), [
                                    'class' => 'btn btn-success btn-sm',
                                    'data-id' => $close_company,
                                    'onclick' => "button = $(this); $.ajax({
                type     :'GET',
                cache    : false,
                url  : $(this).attr('href'),
                success  : function(response) {
                
                if(response == 1) {
                $(\"[data-id=" . $close_company . "]\").text(\"Открыт\");
                $(\"[data-id=" . $close_company . "]\").css(\"background-color\", \"#d9534f\");
                $(\"[data-id=" . $close_company . "]\").css(\"border-color\", \"#c12e2a\");
                } else {
                $(\"[data-id=" . $close_company . "]\").text(\"Закрыт\");
                $(\"[data-id=" . $close_company . "]\").css(\"background-color\", \"#3fad46\");
                $(\"[data-id=" . $close_company . "]\").css(\"border-color\", \"#3fad46\");
                }
                                    
                }
                });
                return false;",
                                ]);
                            } elseif ((($CloseAll == true) && ($CloseCompany == true)) || (($CloseAll == false) && ($CloseCompany == false))) {
                                return Html::a('Открыт', array_merge(['load/close'], ['type' => $GLOBALS['type'], 'company' => $close_company, 'period' => $GLOBALS['pediod']]), [
                                    'class' => 'btn btn-danger btn-sm',
                                    'data-id' => $close_company,
                                    'onclick' => "button = $(this); $.ajax({
                type     :'GET',
                cache    : false,
                url  : $(this).attr('href'),
                success  : function(response) {
                
                if(response == 1) {
                $(\"[data-id=" . $close_company . "]\").text(\"Открыт\");
                $(\"[data-id=" . $close_company . "]\").css(\"background-color\", \"#d9534f\");
                $(\"[data-id=" . $close_company . "]\").css(\"border-color\", \"#c12e2a\");
                } else {
                $(\"[data-id=" . $close_company . "]\").text(\"Закрыт\");
                $(\"[data-id=" . $close_company . "]\").css(\"background-color\", \"#3fad46\");
                $(\"[data-id=" . $close_company . "]\").css(\"border-color\", \"#3fad46\");
                }
                                    
                }
                });
                return false;",
                                ]);
                            }

                        } else {
                            return Html::a('Открыт', array_merge(['load/close'], ['type' => $GLOBALS['type'], 'company' => $close_company, 'period' => $GLOBALS['pediod']]), [
                                'class' => 'btn btn-danger btn-sm',
                                'data-id' => $close_company,
                                'onclick' => "button = $(this); $.ajax({
                type     :'GET',
                cache    : false,
                url  : $(this).attr('href'),
                success  : function(response) {
                
                if(response == 1) {
                $(\"[data-id=" . $close_company . "]\").text(\"Открыт\");
                $(\"[data-id=" . $close_company . "]\").css(\"background-color\", \"#d9534f\");
                $(\"[data-id=" . $close_company . "]\").css(\"border-color\", \"#c12e2a\");
                } else {
                $(\"[data-id=" . $close_company . "]\").text(\"Закрыт\");
                $(\"[data-id=" . $close_company . "]\").css(\"background-color\", \"#3fad46\");
                $(\"[data-id=" . $close_company . "]\").css(\"border-color\", \"#3fad46\");
                }
                                    
                }
                });
                return false;",
                            ]);
                        }

                    },
                ],

            ],
            'contact' => [
                'header' => 'Связь с клиентом',
                'class' => 'kartik\grid\ActionColumn',
                'template'       => '{call}',
                'contentOptions' => ['style' => 'min-width: 50px'],
                'buttons'        => [
                    'call'   => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-earphone"></span>',
                            ['/load/contact', 'id' => $model->partner_id], ['target'=>'_blank']);
                    },
                ]
            ],
            'comment' => [
                'header' => 'Комментарий',
                'class' => 'kartik\grid\ActionColumn',
                'template'       => '{update}',
                'contentOptions' => ['style' => 'min-width: 50px'],
                'buttons'        => [
                    'update' => function ($url, $model, $key) {
                        if ($GLOBALS['company'] == 0) {
                        return ( Html::a('<span class="glyphicon glyphicon-pencil" style="font-size: 15px;"></span>', ['comment', 'type' => $GLOBALS['type'], 'id' => $model->partner_id, 'period' => $GLOBALS['pediod']]));
                        } else {
                        return ( Html::a('<span class="glyphicon glyphicon-pencil" style="font-size: 15px;"></span>', ['comment', 'type' => $GLOBALS['type'], 'id' => $model->id, 'period' => $GLOBALS['pediod'], 'company' => 1]));
                        }
                        },
                ]
            ],
             'buttomClick' => [
                'header' => 'Действия',
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{search}',
                'contentOptions' => ['style' => 'min-width: 50px'],
                'buttons' => [
                    'search' => function ($url, $model, $key) {

                        $prefixHttp = '';

                        if(strpos(Url::to('@frontWeb'), 'http') === false) {
                            $prefixHttp = 'http://';
                        }

                        if($GLOBALS['company']) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                $prefixHttp . Url::to('@frontWeb') . '/act/list?' . urlencode('ActSearch[period]') . '=' . $GLOBALS['pediod'] . '&' . urlencode('ActSearch[client_id]') . '=' . $model->id . '&type=' . $GLOBALS['type'] . '&company=1', ['target' => '_blank']);
                        } else {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                $prefixHttp . Url::to('@frontWeb') . '/act/list?' . urlencode('ActSearch[period]') . '=' . $GLOBALS['pediod'] . '&' . urlencode('ActSearch[partner_id]') . '=' . $model->partner_id . '&type=' . $GLOBALS['type'], ['target' => '_blank']);
                        }

                    },
                ]
            ],
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
                        return ($data->created_at > time() - 3600 * 3) ? Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['update', 'id' => $data->id, 'company' => 1]) : '';
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
                    Service::TYPE_WASH => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_TIRES => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PARKING => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_TIRES => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PARKING => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                ]
            ],
            User::ROLE_WATCHER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_TIRES => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PARKING => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_TIRES => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PARKING => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                ]
//                [
//                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'clientService', 'expense', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'number', 'type', 'expense'],
//                ],
//                [
//                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'partnerService', 'income', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'income'],
//                ]
            ],
            User::ROLE_MANAGER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_TIRES => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PARKING => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                ],
                [
                    Service::TYPE_WASH => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_TIRES => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PARKING => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'name', 'city', 'expense', 'CloseButt', 'contact', 'comment','buttomClick'],
                ]
//                [
//                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'clientService', 'expense', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'number', 'type', 'card', 'expense', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'number', 'type', 'expense'],
//                ],
//                [
//                    Service::TYPE_WASH => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'partnerService', 'income', 'check'],
//                    Service::TYPE_SERVICE => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_TIRES => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'card', 'income', 'viewButtons'],
//                    Service::TYPE_DISINFECT => ['row', 'clientParent', 'client', 'day', 'mark', 'number', 'type', 'income'],
//                ]
            ],
            User::ROLE_PARTNER => [
                [
                    Service::TYPE_WASH => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'partnerService', 'expense', 'check', 'partnerButtons'],
                    Service::TYPE_SERVICE => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_TIRES => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_DISINFECT => ['row', 'partner', 'day', 'mark', 'car', 'type', 'expense'],
                    Service::TYPE_PARKING => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
                    Service::TYPE_PENALTY => ['row', 'partner', 'day', 'mark', 'car', 'type', 'card', 'expense', 'viewButtons'],
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
                    Service::TYPE_PENALTY => ['row', 'clientParent', 'client', 'day', 'mark', 'car', 'type', 'card', 'income', 'city', 'viewButtons'],
                ]
            ],
        ];

        if (!$hasChildren && $assets[$role][$company][$type][1] == 'clientParent') {
            unset($assets[$role][$company][$type][1]);
        }
        return array_intersect_key($columns, array_flip($assets[$role][$company][$type]));
    }
}