<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use \yii\web\View;
use yii\helpers\Url;

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $searchModel common\models\search\CompanyDriverSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$script = <<< JS

$('.uploadPhonesButt').on('click', function(){
// Инициируем нажатие на форму выбора файла
$(".uploadPhones").trigger("click");
});

// Отправляем файл если он был выбран
form = $(".uploadPhonesForm"), upload = $(".uploadPhones");
upload.change(function(){
form.submit();
});

JS;
$this->registerJs($script, View::POS_READY);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= \Yii::$app->controller->action->id == 'driver' ? $searchModel->company->name : $model->name ?><!-- ::  \Yii::$app->controller->action->id == 'driver' ? 'Водители' : 'ТС без водителей' */?>-->
        <div class="header-btn pull-right">
            <?php

            $company_id = 0;

            if(\Yii::$app->controller->action->id == 'driver') {
                $company_id = $searchModel->company_id;
                echo Html::a('ТС без водителей', ['company/undriver', 'id' => $company_id], ['class' => 'btn btn-danger btn-sm', 'style' => 'margin-right:10px;']);
                echo Html::a('Выгрузить', ['company-driver/carsexcel', 'id' => $company_id], ['class' => 'btn btn-success btn-sm', 'style' => 'margin-right:10px;']);
            } else {
                $company_id = $model->id;
                echo Html::a('Водители', ['company/driver', 'id' => $company_id], ['class' => 'btn btn-danger btn-sm', 'style' => 'margin-right:10px;']);
                echo Html::a('Выгрузить', ['company-driver/carsexcel', 'id' => $company_id, 'undriver' => true], ['class' => 'btn btn-success btn-sm', 'style' => 'margin-right:10px;']);
            }

            // Форма загрузки файла со списком
            echo '<span class="btn btn-warning btn-sm uploadPhonesButt">Загрузить</span>';
            echo Html::beginForm(['company-driver/upload', 'company_id' => $company_id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'uploadPhonesForm', 'style' => 'display:none;']);
            echo Html::fileInput("uploadPhones", '',['accept' => '.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel', 'class' => 'uploadPhones', 'style' => 'display:none;']);
            echo Html::endForm();
            // Форма загрузки файла со списком

            ?>

        </div>
    </div>

    <?php

    $GLOBALS['arrTypes'] = $arrTypes;
    $GLOBALS['arrMarks'] = $arrMarks;

    if(\Yii::$app->controller->action->id == 'driver') {

        $actionLinkEmail = Url::to('@web/email/smstext');
        $actionSendSms = Url::to('@web/email/sendsms');

        $script = <<< JS
        if($('.hideButtonModal')) {
        $('.hideButtonModal').remove();
        }

        // открываем модальное окно перенести тс
        $('.showModalSms').on('click', function() {
            $('#showModal').modal('show');
        });

        var emailText = $('.emailText');
        var selID = 0;
        
        // Если почтовый шаблон был изменен
        $('#emailID').on('change', function() {
            if($(this).val() != '') {
            emailText.text('Загрузка..');
            selID = 0;
            
            $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + $(this).val(),
                url  : '$actionLinkEmail',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                emailText.html(response.text);
                selID = response.id;
                } else {
                // Неудачно
                }
                
                }
                });
            
            } else {
            emailText.text('Выберите смс шаблон рассылки');
            }
        });
        
        $('#send_sms').on('click', function() {
            
            if(selID > 0) {
                
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'id=' + selID + '&company_id=' + '$company_id',
                url  : '$actionSendSms',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                selID = 0;
                $('#emailID').val('');
                alert('Успешно отправлкено: ' + response.num + ' смс');
                $('#showModal').modal('hide');
                } else {
                // Неудачно
                alert('Ошибка при отправке смс');
                }
                
                }
                });
                
            } else {
                alert('Выберите смс шаблон рассылки');
            }
            
        });
        
JS;

        $this->registerJs($script, View::POS_READY);

        // Кнопки рассылки
        $filters = '<span class="btn btn-primary btn-sm showModalSms">Отправить СМС рассылку</span>';
        $filters .= Html::a('Добавить водителя', ['company-driver/create', 'company_id' => $company_id], ['class' => 'btn btn-danger btn-sm']);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'filterSelector' => '.ext-filter',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters,
                            'options' => [
                                'style' => 'vertical-align: middle; text-align:right;',
                                'colspan' => 7,
                                'class' => 'kv-grid-group-filter',
                            ],
                        ]
                    ],
                    'options' => ['class' => 'extend-header'],
                ],

            ],
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'header' => 'Марка ТС',
                    'value' => function ($data) {

                        if (isset($data['car']['mark_id'])) {
                            $idMark = $data['car']['mark_id'];
                            return $GLOBALS['arrMarks'][$idMark];
                        }

                    },
                ],
                [
                    'header' => 'Номер ТС',
                    'attribute' => 'car.number',
                ],
                [
                    'header' => 'Тип ТС',
                    'value' => function ($data) {

                        if (isset($data['car']['type_id'])) {
                            $idType = $data['car']['type_id'];
                            return $GLOBALS['arrTypes'][$idType];
                        }

                    },
                ],
                [
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'phone',
                    'value' => function ($data) {

                        $phone = $data->phone;
                        $phone = str_replace(" ", '', $phone);
                        $phone = str_replace("-", '', $phone);

                        return $phone;

                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'contentOptions' => ['align' => 'center'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/company-driver/update', 'id' => $model->id]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/company-driver/delete', 'id' => $model->id], [
                                'data-confirm' => "Are you sure you want to delete this item?",
                                'data-method' => "post",
                                'data-pjax' => "0",
                            ]);
                        },
                    ]
                ],
            ],
        ]);

        $modal = Modal::begin([
            'header' => '<h4>Создание СМС рассылки</h4>',
            'id' => 'showModal',
            'toggleButton' => ['label' => 'открыть окно', 'class' => 'btn btn-default hideButtonModal', 'style' => 'display:none;'],
            'size' => 'modal-lg',
        ]);

        echo "<div style='margin-bottom:15px; font-size:15px; color:#000;'><b>Почтовый шаблон:</b></div>";

        $arrTemplates = \backend\controllers\EmailController::getSmsTemplates();
        echo Html::dropDownList("emailID", 0, $arrTemplates, ['id' => 'emailID', 'class' => 'form-control', 'prompt' => 'Выберите смс шаблон рассылки']);

        echo "<div style='margin-bottom:10px; font-size:15px; color:#000; margin-top:20px;'><b>Текст рассылки:</b></div>";
        echo "<div class='emailText' style='word-wrap: break-word; font-size:13px; color:#000;'>Выберите смс шаблон рассылки</div>";
        echo Html::buttonInput("Отправить рассылку", ['id' => 'send_sms', 'class' => 'btn btn-primary', 'style' => 'margin-top:20px; padding:7px 16px 6px 16px;']);

        Modal::end();

    } else {

        $filters = Html::a('Добавить водителя', ['company-driver/create', 'company_id' => $company_id], ['class' => 'btn btn-danger btn-sm']);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'filterSelector' => '.ext-filter',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters,
                            'options' => [
                                'style' => 'vertical-align: middle; text-align:right;',
                                'colspan' => 4,
                                'class' => 'kv-grid-group-filter',
                            ],
                        ]
                    ],
                    'options' => ['class' => 'extend-header'],
                ],

            ],
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'header' => 'Марка ТС',
                    'value' => function ($data) {
                        return $GLOBALS['arrMarks'][$data->mark_id];
                    },
                ],
                [
                    'header' => 'Номер ТС',
                    'attribute' => 'number',
                ],
                [
                    'header' => 'Тип ТС',
                    'value' => function ($data) {
                        return $GLOBALS['arrTypes'][$data->type_id];
                    },
                ],
            ],
        ]);

    }

    ?>

</div>
