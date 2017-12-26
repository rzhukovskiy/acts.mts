<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\Company;
use yii\web\View;
use yii\helpers\Url;

$actionUpdateMailStatus = Url::to('@web/company-offer/mailstatus');

$script = <<< JS
$('.updateNotific').change(function () {
    
    var valueChecked = 0;
    
    if($(this).prop('checked')) {
        valueChecked = 1;
    } else {
        valueChecked = 0;
    }
    
                $.ajax({
                type     :'POST',
                cache    : false,
                data:'id=' + $(this).data('id') + '&value=' + valueChecked,
                url  : '$actionUpdateMailStatus',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                } else {
                // Неудачно
                alert('Ошибка');
                }
                
                }
                });
    
 });
JS;
$this->registerJs($script, View::POS_READY);

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
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'type',
                    'header' => 'Тип',
                    'group' => true,
                    'groupedRow' => true,
                    'groupOddCssClass' => 'kv-group-header',
                    'groupEvenCssClass' => 'kv-group-header',
                    'value' => function ($data) {
                        return Company::$listType[$data->type]['ru'];
                    },
                ],
                'name',
                [
                    'attribute' => 'info.email',
                    'format' => 'raw',
                    'value' => function ($data) {
                        if (isset($data->info->email)) {
                            $emailShow = strtolower(trim($data->info->email));

                            if (mb_strlen($emailShow) <= 4) {
                                $emailShow = '<span style="color:#c96909">Не указано</span>';
                            } else {

                                if (count(explode(',', $emailShow)) > 1) {

                                    $arrEmail = explode(',', $emailShow);
                                    $checkError = false;

                                    for ($iEmail = 0; $iEmail < count($arrEmail); $iEmail++) {

                                        $emailContent = trim($arrEmail[$iEmail]);

                                        if (!filter_var($emailContent, FILTER_VALIDATE_EMAIL)) {
                                            $checkError = true;
                                        }

                                    }

                                    if ($checkError == false) {
                                        $emailShow = $data->info->email;
                                    } else {
                                        $emailShow = '<span style="color:#d80000"><b>' . $data->info->email . '</b></span>';
                                    }

                                } else {
                                    if (filter_var($emailShow, FILTER_VALIDATE_EMAIL)) {

                                        $emailShow = $data->info->email;

                                    } else {
                                        $emailShow = '<span style="color:#d80000"><b>' . $data->info->email . '</b></span>';
                                    }
                                }

                            }

                            return $emailShow;
                        } else {
                            return '';
                        }
                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view}',
                    'contentOptions' => ['style' => 'width: 90px'],
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/state', 'id' => $model->id]);
                        },
                    ],
                ],
                [
                    'attribute' => 'offer.email_status',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-center'],
                    'options' => [
                        'style' => 'width: 90px',
                    ],
                    'value' => function ($data) {
                        if (isset($data->offer->email_status)) {
                        if($data->offer->email_status == 1) {
                            return '<input type="checkbox" class="updateNotific" data-id="' . $data->id . '" checked>';
                        } else {
                            return '<input type="checkbox" class="updateNotific" data-id="' . $data->id . '">';
                        }
                        } else {
                            return '';
                        }
                    },
                ],
            ],
        ]);

        ?>

    </div>

</div>