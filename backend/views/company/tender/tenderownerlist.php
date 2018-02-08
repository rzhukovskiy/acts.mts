<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\models\User;
use yii\helpers\Url;
use common\models\TenderOwner;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Распределение тендеров';

$actionLinkGetComments = Url::to('@web/company/getcomments');
$isAdmin = (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) ? 1 : 0;
$ajaxstatus = Url::to('@web/company/ajaxstatus');

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


window.onload=function(){           
       var companyTR = $('tbody tr');
       var numCount = 0;
       var userName = [];
       var i = 0;
       var userOld = '';
       var userNow = '';
       var resTables = '';
       var resUsers = '';
       var nameTabs = '';
       
     $(companyTR).each(function (id, value) {
        var thisId = $(this);
        if(!(thisId.find('td div').hasClass('empty'))) {
             if (thisId.attr('class') == "kv-grid-group-row") {
                 userNow = thisId.find($('td[data-even-css="kv-group-header"]')).text();
                  if (i == 0) {
                      userName[userNow] = 0;
                  } else {
                      userName[userOld] = numCount;
                  }
                  numCount = 0;
           } else if (thisId.attr('class') == "kv-page-summary warning") {
                 if (i > 0) {
                 userName[userNow] = numCount;
                 }
           } else if (thisId.attr('data-key') > 0) {
                numCount++;
           }
        }
        userOld = userNow;
        i++;
     });
    
       for (var key in userName) {
    if (userName.hasOwnProperty(key)) {
        resUsers += '<tr style="background: #fff; font-weight: normal;"><td style="padding: 3px 5px 3px 5px">'+ key +'</td><td style="padding: 3px 5px 3px 5px">' + userName[key] + '</td></tr>';
    }
}
// Подсчет кол.
            if ($win == 2) {
           nameTabs = 'Количество в архиве';
            } else if ($win == 0) {
           nameTabs = 'Количество в работе';
            }
            resTables = '<table width="100%" border="1" bordercolor="#dddddd" style="margin: 15px 0px 15px 0px;">' +
             '<tr style="background: #428bca; color: #fff;">' +
              '<td colspan="3" style="padding: 3px 5px 3px 5px; font-weight: normal;" align="center">' + nameTabs + '</td>' +
               '</tr>' +
                '<tr style="background: #fff; font-weight: normal;">' + resUsers + '</tr></table>';
           if($win == 2 || $win == 0) {
            $('.place_list').html(resTables);
            }
       };
       
// открываем модальное окно добавить вложения
$('.showFormAttachButt').on('click', function(){
$('#showFormAttach').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');

$('.change-status').change(function(){
       
     var select=$(this);
        $.ajax({
            url: '$ajaxstatus',
            type: "post",
            data: {status:$(this).val(),id:$(this).data('id')},
            success: function(data){
                select.parent().attr('class',data);
                if(($isAdmin!=1)&&(select.data('status')!=1)){
                    select.attr('disabled', 'disabled');
                }
            }
        });
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$modalAttach = Modal::begin([
    'header' => '<h5>Добавить Exel</h5>',
    'id' => 'showFormAttach',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div style='font-size: 15px; margin-left:15px;'>Выберите файл:</div>";

$form = ActiveForm::begin([
    'action' => ['/company/uploadtenderexel'],
    'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '<div class="col-sm-6">{input}</div>',
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]);

echo Html::fileInput("files", '',['accept' => '.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']) . '<br />';

echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']);

ActiveForm::end();

Modal::end();
echo Tabs::widget([
    'items' => [
        ['label' => 'Новые', 'url' => ['company/tenderownerlist?win=1'], 'active' => $win == 1],
        ['label' => 'В работе', 'url' => ['company/tenderownerlist?win=0'], 'active' => $win == 0 || $win == 256 || $win == 654 || $win == 756],
        ['label' => 'Архив', 'url' => ['company/tenderownerlist?win=2'], 'active' => $win == 2],
        ['label' => 'Не взяли', 'url' => ['company/tenderownerlist?win=3'], 'active' => $win == 3],
    ],
]);

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
        'attribute' => 'number',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->number) {
                return $data->number;
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'customer',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->customer) {
                return $data->customer;
            } else {
                return '-';
            }

        },
    ],
    [
    'attribute' => 'purchase_name',
    'contentOptions' => ['style' => 'min-width: 330px'],
    'vAlign'=>'middle',
    'value' => function ($data) {

        if ($data->purchase_name) {
            return $data->purchase_name;
        } else {
            return '-';
        }

    },
    ],
    [
        'attribute' => 'fz',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->fz) {
                return $data->fz;
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'purchase',
        'vAlign'=>'middle',
        'contentOptions' => ['style' => 'min-width: 115px'],
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
                return date('d.m.Y H:i', $data->date_from);
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
                return date('d.m.Y H:i', $data->date_to);
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
        'attribute' => 'electronic_platform',
        'header' => 'Эл. площадка',
        'vAlign'=>'middle',
        'contentOptions' => ['style' => 'text-align: center'],
        'format' => 'raw',
        'value' => function ($data) {

            if (isset($data->electronic_platform)) {
                if ($data->electronic_platform) {
                    $platform = str_replace('http://', '',$data->electronic_platform);
                    return str_replace('https://', '',$platform);
                } else {
                    return '-';
                }
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'link_official',
        'header' => 'Док-я',
        'vAlign'=>'middle',
        'format' => 'raw',
        'contentOptions' => ['style' => 'text-align: center'],
        'value' => function ($data) {

            if (isset($data->link_official)) {
                if ($data->link_official) {
                    return Html::a('ссылка', $data->link_official, ['target' => '_blank']);
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
        'contentOptions' => ['style' => 'text-align: center'],
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
        'attribute' => 'status',
        'format' => 'raw',
        'vAlign'=>'middle',
        'contentOptions' => ['style' => 'min-width: 150px; vertical-align: middle'],
        'value' => function ($data, $key, $index, $column) {
            return Html::activeDropDownList($data, 'status', TenderOwner::$status,
                [
                    'class'              => 'form-control change-status',
                    'data-id'            => $data->id,
                    'data-status'        => $data->status,
                ]

            );
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
            'attribute' => 'status',
            'content' => function ($data) {

                if (isset($data->status)) {
                    return TenderOwner::$status[$data->status];
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
            'format' => 'raw',
            'header' => 'Текст',
            'value' => function ($data) {

                if ($data->text) {
                    return '<span class="showStatus">' . $data->text . '</span>';
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($data, $key, $index, $column){
                return ['data-owner' => $data->id];
            },
        ],
        [
            'attribute' => 'number',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->number) {
                    return $data->number;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'customer',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->customer) {
                    return $data->customer;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'purchase_name',
            'vAlign'=>'middle',
            'format' => 'raw',
            'value' => function ($data) {

                if ($data->purchase_name) {
                    return '<span class="showStatus">' . $data->purchase_name . '</span>';
                } else {
                    return '-';
                }

            },
            'contentOptions' =>function ($data, $key, $index, $column){
                return ['data-owner' => $data->id];
            },
        ],
        [
            'attribute' => 'fz',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->fz) {
                    return $data->fz;
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
                    return date('d.m.Y H:i', $data->date_from);
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
                    return date('d.m.Y H:i', $data->date_to);
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
            'attribute' => 'electronic_platform',
            'header' => 'Эл. площадка',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'text-align: center'],
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->electronic_platform)) {
                    if ($data->electronic_platform) {
                        $platform = str_replace('http://', '',$data->electronic_platform);
                        return str_replace('https://', '',$platform);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link_official',
            'vAlign'=>'middle',
            'header' => 'Док-я',
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->link_official)) {
                    if ($data->link_official) {
                        return Html::a('ссылка', $data->link_official, ['target' => '_blank']);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'min-width: 150px; vertical-align: middle'],
            'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
            'value' => function ($data, $key, $index, $column) {
                return Html::activeDropDownList($data, 'status', TenderOwner::$status,
                    [
                        'class'              => 'form-control change-status',
                        'data-id'            => $data->id,
                        'data-status'        => $data->status,
                    ]

                );
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
} else if ($win == 0) {
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
            'value' => function ($data) {

                if ($data->text) {
                    return $data->text;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'number',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->number) {
                    return $data->number;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'customer',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->customer) {
                    return $data->customer;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'purchase_name',
            'contentOptions' => ['style' => 'min-width: 330px'],
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->purchase_name) {
                    return $data->purchase_name;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'fz',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->fz) {
                    return $data->fz;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'purchase',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'contentOptions' => ['style' => 'min-width: 115px'],
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
                    return date('d.m.Y H:i', $data->date_from);
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
                    return date('d.m.Y H:i', $data->date_to);
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
            'attribute' => 'electronic_platform',
            'header' => 'Эл. площадка',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'text-align: center'],
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->electronic_platform)) {
                    if ($data->electronic_platform) {
                        $platform = str_replace('http://', '',$data->electronic_platform);
                        return str_replace('https://', '',$platform);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link_official',
            'header' => 'Док-я',
            'vAlign'=>'middle',
            'format' => 'raw',
            'contentOptions' => ['style' => 'text-align: center'],
            'value' => function ($data) {

                if (isset($data->link_official)) {
                    if ($data->link_official) {
                        return Html::a('ссылка', $data->link_official, ['target' => '_blank']);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'vAlign'=>'middle',
            'format' => 'raw',
            'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN ? false : true,
            'contentOptions' => ['style' => 'text-align: center'],
            'header' => 'Отправить<br />в закупки',
            'value' => function ($data) {

             if (!isset($data->tender_id)) {
                if (!$data->tender_id) {
                    return Html::a('Отправить', ['/company/sendtotender', 'id' => $data->id], ['class' => 'btn btn-success btn-sm']);
                } else {
                    return '';
                }
             } else {
                    return '';
             }
            },
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'min-width: 150px; vertical-align: middle'],
            'value' => function ($data, $key, $index, $column) {
                return Html::activeDropDownList($data, 'status', TenderOwner::$status,
                    [
                        'class'              => 'form-control change-status',
                        'data-id'            => $data->id,
                        'data-status'        => $data->status,
                    ]

                );
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
            'value' => function ($data) {

                if ($data->text) {
                    return $data->text;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'number',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->number) {
                    return $data->number;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'customer',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->customer) {
                    return $data->customer;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'purchase_name',
            'contentOptions' => ['style' => 'min-width: 330px'],
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->purchase_name) {
                    return $data->purchase_name;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'fz',
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->fz) {
                    return $data->fz;
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'purchase',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'contentOptions' => ['style' => 'min-width: 115px'],
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
                    return date('d.m.Y H:i', $data->date_from);
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
                    return date('d.m.Y H:i', $data->date_to);
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
            'attribute' => 'electronic_platform',
            'header' => 'Эл. площадка',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'text-align: center'],
            'format' => 'raw',
            'value' => function ($data) {

                if (isset($data->electronic_platform)) {
                    if ($data->electronic_platform) {
                        $platform = str_replace('http://', '',$data->electronic_platform);
                        return str_replace('https://', '',$platform);
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }

            },
        ],
        [
            'attribute' => 'link_official',
            'header' => 'Док-я',
            'vAlign'=>'middle',
            'format' => 'raw',
            'contentOptions' => ['style' => 'text-align: center'],
            'value' => function ($data) {

                if (isset($data->link_official)) {
                    if ($data->link_official) {
                        return Html::a('ссылка', $data->link_official, ['target' => '_blank']);
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

$delete = TenderOwner::find()->where(['AND', ['<', 'date_to', time()], ['!=', 'date_to', '']])->andWhere(['AND', ['tender_user' => 0], ['is', 'reason_not_take', null]])->orWhere(['AND', ['tender_user' => 0], ['reason_not_take' => '']])->select('id')->column();
    if (count($delete) > 0) {
       for ($i = 0; $i < count($delete); $i++) {
        $modelSet = TenderOwner::findOne(['id' => $delete[$i]]);
        $modelSet->status = 11;
        $modelSet->save();
       }
    }

$statTable = '<div class="place_list"></div>';

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Распределение тендеров
        <div class="header-btn pull-right">
            <?= Yii::$app->user->identity->role == User::ROLE_ADMIN ? Html::a('Добавить', ['company/tenderowneradd'], ['class' => 'btn btn-success btn-sm']) . '&nbsp&nbsp<span class="btn btn-warning btn-sm showFormAttachButt" style="margin-right:15px;">Добавить Exel</span>' : '' ?>
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
            'showPageSummary' => ($win == 0 || $win == 2) ? true : '',
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
