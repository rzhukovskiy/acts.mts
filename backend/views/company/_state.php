<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\Modal;

$actionLink = Url::to('@web/company/getcomment');

$script = <<< JS

// Удаляем ненужную кнопку открыть модальное окно
$('.hideButtonComment').remove();

// Добавляем дату к файлам в модальном окне
if($('.attachDate').length > 0) {

   $('.attachDate').each(function(){
       
       var dateText = $('tbody tr[data-key=' + $(this).data("id") + '] td[data-col-seq=0]').text();
       
       $(this).text(dateText);
       
       dateText = '';
   });
}

// открываем модальное окно полный комментарий
$('tbody tr td[data-col-seq=5]').on('click', '.showFullComment', function(){
    
            $.ajax({
                type     :'POST',
                cache    : false,
                data:'state=' + $(this).data("comment"),
                url  : '$actionLink',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                $('#fullComment').html(response.comment);
                $('#showModal').modal('show');
                } else {
                // Неудачно
                }
                
                }
                });
    
});

// открываем модальное окно все вложения
$('.showAttachButt').on('click', function(){
$('#showModalAttach').modal('show');
});

JS;
$this->registerJs($script, View::POS_READY);

$css = ".showFullComment {text-decoration:underline; font-size:14px; color:#428bca;} .showFullComment:hover {text-decoration:none; font-size:14px; color:#069; cursor:pointer;} #fullComment {font-size:14px;}";
$this->registerCss($css);

$GLOBALS['companyMembers'] = $companyMembers;
$GLOBALS['authorMembers'] = $authorMembers;
$GLOBALS['types'] = ['0' => 'Исходящий звонок' , '1' => 'Входящий звонок', '2' => 'Исходящее письмо', '3' => 'Входящее письмо'];

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $modelCompanyInfo->company->name ?> :: полезная информация
    </div>
    <div class="panel-body">
        <table class="table table-bordered list-data">

            <tr>
                <td class="list-label-md">Местное время</td>
                <td>
                    <?php

                    $showTimeLocation = '';
                    $timeCompany = time() + (3600 * $modelCompanyInfo->time_location);

                    if($modelCompanyInfo->time_location == 0) {
                        $showTimeLocation = date('H:i', $timeCompany);
                    } else {
                        if($modelCompanyInfo->time_location > 0) {
                            $showTimeLocation = date('H:i', $timeCompany) . ' (' . '+' . $modelCompanyInfo->time_location . ')';
                        } else {
                            $showTimeLocation = date('H:i', $timeCompany) . ' (' . $modelCompanyInfo->time_location . ')';
                        }
                    }

                    $editableForm = Editable::begin([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'time_location',
                        'displayValue' => $showTimeLocation,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Местное время', 'style' => 'display:none;'],
                        'formOptions' => [
                            'action' => ['/company-info/updatetimelocation', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);

                    $form = $editableForm->getForm();
                    echo Html::hiddenInput('kv-complex', '1');

                    $editableForm->afterInput = '' . $form->field($modelCompanyInfo, 'time_location')->dropDownList(['-12' => -12, '-11' => -11, '-10' => -10, '-9' => -9, '-8' => -8, '-7' => -7, '-6' => -6, '-5' => -5, '-4' => -4, '-3' => -3, '-2' => -2, '-1' => -1, 0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12], ['class' => 'form-control', 'options'=>[$modelCompanyInfo->time_location => ['Selected'=>true]]]) . '';
                    Editable::end();

                    ?>
                </td>
            </tr>

            <tr>
                <td class="list-label-md"><?= $modelCompanyOffer->getAttributeLabel('communication_str') ?></td>
                <td>
                    <?php

                    $wekCommunicDate = '';

                    if(isset($modelCompanyOffer->communication_str)) {

                        try {
                            $CommunicDate = strtotime($modelCompanyOffer->communication_str);
                            $wekCommunicDate = date("w", $CommunicDate);

                            switch ($wekCommunicDate) {
                                case 1:
                                    $wekCommunicDate = 'Пн.';
                                    break;
                                case 2:
                                    $wekCommunicDate = 'Вт.';
                                    break;
                                case 3:
                                    $wekCommunicDate = 'Ср.';
                                    break;
                                case 4:
                                    $wekCommunicDate = 'Чт.';
                                    break;
                                case 5:
                                    $wekCommunicDate = 'Пт.';
                                    break;
                                case 6:
                                    $wekCommunicDate = 'Сб.';
                                    break;
                                case 7:
                                    $wekCommunicDate = 'Вс.';
                                    break;
                            }

                            $wekCommunicDate = $modelCompanyOffer->communication_str . ' (' . $wekCommunicDate . ')';
                        } catch (\Exception $e) {
                            $wekCommunicDate = $modelCompanyOffer->communication_str;
                        }

                    } else {
                        $wekCommunicDate = $modelCompanyOffer->communication_str;
                    }

                    echo Editable::widget([
                        'model' => $modelCompanyOffer,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'communication_str',
                        'displayValue' => $wekCommunicDate,
                        'inputType' => Editable::INPUT_DATETIME,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => [
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy hh:ii',
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                        ],
                        'formOptions' => [
                            'action' => ['/company-offer/update', 'id' => $modelCompanyOffer->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>

        </table>
    </div>

</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        Статус клиента :: ИСТОРИЯ
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['company/newstate', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
        <div class="header-btn pull-right">
            <span class="pull-right btn btn-warning btn-sm showAttachButt" style="margin-right:15px;">Просмотр всех вложений</span>
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
        'columns' => [
            [
                'attribute' => 'date',
                'value' => function ($data) {
                    return date('H:i d.m.Y', $data->date);
                },
            ],
            [
                'attribute' => 'member_id',
                'format' => 'raw',
                'value' => function ($data) {

                $arrMemberList = explode(', ', $data->member_id);
                $memberText = '';

                if(count($arrMemberList) > 1) {

                    for($i = 0; $i < count($arrMemberList); $i++) {
                        $memberText .= $GLOBALS['companyMembers'][$arrMemberList[$i]] . '<br />';
                    }

                } else {

                    try {
                        $memberText = $GLOBALS['companyMembers'][$data->member_id];
                    } catch (\Exception $e) {
                        $memberText = '-';
                    }

                }

                return $memberText;

                },
            ],
            [
                'attribute' => 'author_id',
                'value' => function ($data) {
                    return $GLOBALS['authorMembers'][$data->author_id];
                },
            ],
            [
                'attribute' => 'type',
                'value' => function ($data) {
                    return $GLOBALS['types'][$data->type];
                },
            ],
            [
                'header' => 'Вложения',
                'format' => 'raw',
                'value' => function ($data) {

                    $pathfolder = \Yii::getAlias('@webroot/files/attaches/' . $data->company_id . '/');
                    $shortPath = '/files/attaches/' . $data->company_id . '/';

                    if (file_exists($pathfolder)) {

                        $numFiles = 0;
                        $resLinksFiles = '';
                        $arrStateID = [];

                        foreach (FileHelper::findFiles($pathfolder) as $file) {

                            $arrStateID = explode('-', basename($file));

                            if($arrStateID[0] == $data->id) {
                                $resLinksFiles .= Html::a(str_replace($data->id . '-', '', basename($file)), $shortPath . basename($file), ['target'=>'_blank']) . '<br />';
                                $numFiles++;
                            }

                            $arrStateID = [];

                        }

                        if($numFiles > 0) {
                            return $resLinksFiles;
                        } else {
                            return '-';
                        }

                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'comment',
                'format' => 'raw',
                'options' => [
                    'style' => 'width: 630px',
                ],
                'value' => function ($data) {

                    $commText = mb_convert_encoding($data->comment, "utf-8");

                    if(mb_strlen($commText) > 530) {

                        $comment = mb_substr(nl2br($commText), 0, 528) . '.. ' . '<div class="showFullComment" data-comment="' . $data->id . '">Подробнее</div>';
                        return $comment;

                    } else {
                        return nl2br($commText);
                    }

                },
            ],
        ],
    ]);

    $modal = Modal::begin([
        'header' => '<h4>Подробный комментарий</h4>',
        'id' => 'showModal',
        'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
        'size'=>'modal-lg',
    ]);
    echo "<div id='fullComment'></div>";
    Modal::end();

    // Модальное окно показать все вложения
    $pathfolder = \Yii::getAlias('@webroot/files/attaches/' . $model->id . '/');
    $shortPath = '/files/attaches/' . $model->id . '/';

    $numFiles = 0;
    $resLinksFiles = '';

    if (file_exists($pathfolder)) {

        foreach (FileHelper::findFiles($pathfolder) as $file) {
            if((basename($file) != 'attaches.zip') && (basename($file) != '.DS_Store')) {
                $arrStateID = explode('-', basename($file));
                $resLinksFiles .= '<span class="attachDate" data-id="' . $arrStateID[0] . '" style="color:#757575; margin-right:10px;"></span>' . Html::a(str_replace($arrStateID[0] . '-', '', basename($file)), $shortPath . basename($file), ['target' => '_blank']) . '<br />';
                $numFiles++;
            }
        }

    }

    $modalAttach = Modal::begin([
        'header' => '<h4>Все вложения</h4>',
        'id' => 'showModalAttach',
        'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
        'size'=>'modal-lg',
    ]);

    if($numFiles == 0) {
        $resLinksFiles = '<span style="color:#757575;">Нет вложений.</span>';
    } else {
        $actionAttachLink = Url::to(['@web/company/attaches', 'id' => $model->id]);
        $resLinksFiles = Html::a('<span class="pull-left btn btn-primary btn-sm">Скачать все одним файлом</span>', $actionAttachLink) . '<br /><br />' . $resLinksFiles;
    }

    echo "<div id='allAttach' style='font-size: 15px;'>" . $resLinksFiles . "</div>";
    Modal::end();
    // Модальное окно показать все вложения

    ?>
    </div>
</div>
