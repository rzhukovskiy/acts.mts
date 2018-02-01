<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\models\User;
use yii\helpers\Url;
use common\models\TenderOwner;

$this->title = 'Распределение тендеров';

$actionLinkGetComments = Url::to('@web/company/getcomments');

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

$script = <<< JS

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
            
                if($(this).parent().data('owner') > 0) {
        
                var idKey = $(this).parent().data('owner');
                if(typeof(arrRessComm[idKey]) != "undefined" && arrRessComm[idKey] !== null) {
            this.t = this.title;
            this.title = "";

            margTop = window.event.clientY - 20;
            margLeft = window.event.clientX + document.body.scrollLeft + 25;
            
            $("#previewStatus").css("top", margTop + "px")
            .css("left", margLeft + "px")
            .fadeIn("fast");
                    $("body").append("<p id='previewStatus'>" + arrRessComm[idKey] + "</p>");

                openWindowComm = false;
                } else {
                
                    $("body").append("<p id='previewStatus'></p>");
                    
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + idKey,
                url  : '$actionLinkGetComments',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                arrRessComm[idKey] = response.comment;
                
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
            
            if($("#previewStatus")) {
                $("#previewStatus").remove(); 
            }
            
            if(typeof(arrRessComm[idKey]) != "undefined" && arrRessComm[idKey] !== null) {
                $("body").append("<p id='previewStatus'>" + arrRessComm[idKey] + "</p>");
            } else {
                $("body").append("<p id='previewStatus'><u style='color:#757575;'>Комментарий:</u></p>");
            }

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
$this->registerJs($script, \yii\web\View::POS_READY);

echo Tabs::widget([
    'items' => [
        ['label' => 'Новые', 'url' => ['company/tenderownerlist?win=1'], 'active' => $win == 1],
        ['label' => 'В работе', 'url' => ['company/tenderownerlist?win=0'], 'active' => $win == 0 || $win == 256 || $win == 654 || $win == 756],
        ['label' => 'Архив', 'url' => ['company/tenderownerlist?win=2'], 'active' => $win == 2],
        ['label' => 'Не взяли', 'url' => ['company/tenderownerlist?win=3'], 'active' => $win == 3],
    ],
]);

// подсчет количества тендеров в работе
$userDen = TenderOwner::find()->Where(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->andWhere(['tender_user' => 256])->count();
$userAlyona = TenderOwner::find()->Where(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->andWhere(['tender_user' => 654])->count();
$userMasha = TenderOwner::find()->Where(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->andWhere(['tender_user' => 756])->count();
// конец подсчет количества тендеров в работе

if ($win == 1) {
$collumn = [

    [
        'header' => '№',
        'vAlign'=>'middle',
        'class' => 'kartik\grid\SerialColumn'
    ],
    [
        'attribute' => 'text',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->text) {
                return $data->text;
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'purchase',
        'vAlign'=>'middle',
        'contentOptions' => ['style' => 'min-width: 130px'],
        'value' => function ($data) {

            if ($data->purchase) {
                return $data->purchase  . ' ₽';
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'date_from',
        'vAlign'=>'middle',
        'filter' => false,
        'value' => function ($data) {

            if ($data->date_from) {
                return date('d.m.Y', $data->date_from);
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'date_to',
        'vAlign'=>'middle',
        'filter' => false,
        'value' => function ($data) {

            if ($data->date_to) {
                return date('d.m.Y', $data->date_to);
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'city',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->city) {
                return $data->city;
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'link',
        'vAlign'=>'middle',
        'format' => 'raw',
        'value' => function ($data) {

            if (isset($data->link)) {
                if ($data->link) {
                    return Html::a('ссылка', $data->link, ['target' => '_blank']);
                } else {
                    return '-';
                }
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'tender_user',
        'vAlign'=>'middle',
        'format' => 'raw',
        'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
        'header' => 'Ответственный<br />сотрудник',
        'value' => function ($data) {

            if ($data->tender_user == 0) {
                return Html::a('Забрать', ['/company/pickup', 'id' => $data->id, 'tender_user' => Yii::$app->user->identity->id, 'data' => strtotime(date("d-m-Y"))], ['class' => 'btn btn-success btn-sm']);
            } else {
                return '';
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
            'update' => function ($url, $data, $key) {
                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                    ['/company/tenderownerfull', 'id' => $data->id]);
            },
            'delete' => function ($url, $data, $key) {
                if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company/ownerdelete', 'id' => $data->id],
                    ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                }
            },
        ],
    ],
];
} else if ($win == 3) {
    $collumn = [
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'text',
            'vAlign'=>'middle',
            'format' => 'raw',
            'header' => 'Текст',
            'value' => function ($data) {

                if ($data->text) {
                    return '<span class="showStatus">' . $data->text . '</span>';
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($model, $key, $index, $column){
                return ['data-owner' => $model->id];
            },
        ],
        [
            'attribute' => 'purchase',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'min-width: 130px'],
            'value' => function ($data) {

                if ($data->purchase) {
                    return $data->purchase  . ' ₽';
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'date_from',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->date_from) {
                    return date('d.m.Y', $data->date_from);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'date_to',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->date_to) {
                    return date('d.m.Y', $data->date_to);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'city',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->city) {
                    return $data->city;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link',
            'vAlign'=>'middle',
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->link)) {
                    if ($data->link) {
                        return Html::a('ссылка', $data->link, ['target' => '_blank']);
                    } else {
                        return '-';
                    }
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
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/company/tenderownerfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company/ownerdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
                },
            ],
        ],
    ];
} else {
    $collumn = [
        [
            'header' => 'Имя сотрудника',
            'content' => function ($data) {

                if (isset($data->username)) {
                    return $data->username;
                } else {
                    return '-';
                }
            },
            'group' => true,
            'groupedRow' => true,
            'groupOddCssClass' => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
        ],
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'text',
            'vAlign'=>'middle',
            'header' => 'Текст',
            'value' => function ($data) {

                if ($data->text) {
                    return $data->text;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'purchase',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'min-width: 130px'],
            'value' => function ($data) {

                if ($data->purchase) {
                    return $data->purchase  . ' ₽';
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'date_from',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->date_from) {
                    return date('d.m.Y', $data->date_from);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'date_to',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->date_to) {
                    return date('d.m.Y', $data->date_to);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'city',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->city) {
                    return $data->city;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'data',
            'vAlign'=>'middle',
            'filter' => false,
            'value' => function ($data) {

                if ($data->data) {
                    return date('d.m.Y', $data->data);
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link',
            'vAlign'=>'middle',
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->link)) {
                    if ($data->link) {
                    return Html::a('ссылка', $data->link, ['target' => '_blank']);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => 'Действие',
            'vAlign'=>'middle',
            'template' => '{link}{update}{delete}',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'buttons' => [
                'link' => function ($url, $data, $key) {
                    if (isset($data->tender_id)) {
                        return Html::a('<span class="glyphicon glyphicon-new-window" style="font-size: 17px;"></span>',
                            ['company/fulltender', 'tender_id' => $data->tender_id]);
                    } else {
                        return '';
                    }
                },
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/company/tenderownerfull', 'id' => $data->id]);
                },
                'delete' => function ($url, $data, $key) {
                    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company/ownerdelete', 'id' => $data->id],
                            ['data-confirm' => "Вы уверены, что хотите удалить?"]);
                    }
                },
            ],
        ],
    ];
}

$statTable = '';
$statTable .= '<table width="100%" border="1" bordercolor="#dddddd" style="margin: 15px 0px 15px 0px;">
                <tr style="background: #428bca; color: #fff;">
                    <td colspan="3" style="padding: 3px 5px 3px 5px; font-weight: normal;" align="center">В работе</td>
                </tr>';
$statTable .=  '</td>
                </tr>
                <tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Денис Митрофанов</td>
                    <td class="userDen" width="300px;" style="padding: 3px 5px 3px 5px">' . $userDen .'</td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>", "/company/tenderownerlist?win=" . 256);

$statTable .=  '<tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Алена Попова</td>
                    <td class="userAlyna" width="300px;" style="padding: 3px 5px 3px 5px">' . $userAlyona .'</td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>", "/company/tenderownerlist?win=" . 654);

$statTable .= '</td>
                </tr>
                <tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Мария Губарева</td>
                    <td class="userMasha" width="300px;" style="padding: 3px 5px 3px 5px">' . $userMasha .'</td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>",  "/company/tenderownerlist?win=" . 756);

$statTable .= '</td>
                </tr>
                <tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Всего</td>
                    <td class="userMasha" width="300px;" style="padding: 3px 5px 3px 5px">' . ($userDen + $userAlyona + $userMasha) .'</td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>",  "/company/tenderownerlist?win=" . 0);

$statTable .= '</td>
                </tr>
            </table>';
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Распределение тендеров
        <div class="header-btn pull-right">
            <?= Yii::$app->user->identity->role == User::ROLE_ADMIN ? Html::a('Добавить', ['company/tenderowneradd'], ['class' => 'btn btn-success btn-sm']) : '' ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $statTable,
                            'options' => [
                                'colspan' => count($collumn),
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-grid-group-filter'],
                ],
            ],
            'columns' => $collumn,
        ]);
        ?>
    </div>
</div>
