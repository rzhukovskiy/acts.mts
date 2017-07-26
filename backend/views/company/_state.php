<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use yii\base\DynamicModel;
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
/*if($('.attachDate').length > 0) {

   $('.attachDate').each(function(){
       
       var dateText = $('tbody tr[data-key=' + $(this).data("id") + '] td[data-col-seq=0]').text();
       
       if(dateText.length > 0) {
           $(this).text(dateText);
       }
       
       dateText = '';
   });
}*/

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

// открываем модальное окно добавить вложения
$('.showFormAttachButt').on('click', function(){
$('#showFormAttach').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');

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
                <td class="list-label-md"><?= $model->getAttributeLabel('name') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'name',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите название'],
                        'formOptions' => [
                            'action' => ['/company/update', 'id' => $model->id],
                        ],
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">Адрес организации</td>
                <td>
                    <?php
                    $editable = Editable::begin([
                        'model' => $modelCompanyInfo,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'city',
                        'displayValue' => $modelCompanyInfo->fullAddress,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите город'],
                        'formOptions' => [
                            'action' => ['/company-info/update', 'id' => $modelCompanyInfo->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    $form = $editable->getForm();
                    echo Html::hiddenInput('kv-complex', '1');
                    $editable->afterInput =
                        $form->field($modelCompanyInfo, 'street') .
                        $form->field($modelCompanyInfo, 'house') .
                        $form->field($modelCompanyInfo, 'index');
                    Editable::end();
                    ?>
                </td>
            </tr>

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

                        if (mb_strlen($modelCompanyOffer->communication_str) > 1) {

                            try {
                                $CommunicDate = strtotime($modelCompanyOffer->communication_str);
                                $wekCommunicDate = date("w", $CommunicDate);

                                switch ($wekCommunicDate) {
                                    case 1:
                                        $wekCommunicDate = 'Понедельник';
                                        break;
                                    case 2:
                                        $wekCommunicDate = 'Вторник';
                                        break;
                                    case 3:
                                        $wekCommunicDate = 'Среда';
                                        break;
                                    case 4:
                                        $wekCommunicDate = 'Четверг';
                                        break;
                                    case 5:
                                        $wekCommunicDate = 'Пятница';
                                        break;
                                    case 6:
                                        $wekCommunicDate = 'Суббота';
                                        break;
                                    case 7:
                                        $wekCommunicDate = 'Воскресение';
                                        break;
                                }

                                $wekCommunicDate = $modelCompanyOffer->communication_str . ' (' . $wekCommunicDate . ')';
                            } catch (\Exception $e) {
                                $wekCommunicDate = $modelCompanyOffer->communication_str;
                            }

                        } else {
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

            <tr>
                <td class="list-label-md"><?= $modelCompanyOffer->getAttributeLabel('process') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $modelCompanyOffer,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'placement' => PopoverX::ALIGN_LEFT,
                        'submitOnEnter' => false,
                        'attribute' => 'process',
                        'displayValue' => $modelCompanyOffer->processHtml,
                        'inputType' => Editable::INPUT_TEXTAREA,
                        'asPopover' => true,
                        'size' => 'lg',
                        'editableValueOptions' => ['style' => 'text-align: left'],
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий', 'style' => 'text-align: left', 'rows' => 7],
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
            <span class="pull-right btn btn-warning btn-sm showFormAttachButt" style="margin-right:15px;">Добавить вложение</span>
        </div>
        <div class="header-btn pull-right">
            <span class="pull-right btn btn-danger btn-sm showAttachButt" style="margin-right:15px;">Просмотр всех вложений</span>
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
                'options' => [
                    'style' => 'width: 135px',
                ],
                'value' => function ($data) {
                    return date('H:i d.m.Y', $data->date);
                },
            ],
            [
                'attribute' => 'member_id',
                'format' => 'raw',
                'options' => [
                    'style' => 'width: 250px',
                ],
                'value' => function ($data) {

    if($data->member_id == 0) {
        return '-';
    } else {

        $arrMemberList = explode(', ', $data->member_id);
        $memberText = '';

        if (count($arrMemberList) > 1) {

            for ($i = 0; $i < count($arrMemberList); $i++) {
                if(isset($GLOBALS['companyMembers'][$arrMemberList[$i]])) {
                    $memberText .= $GLOBALS['companyMembers'][$arrMemberList[$i]] . '<br />';
                } else {
                    $memberText .= 'Сотрудник удален<br />';
                }
            }

        } else {

            try {
                if(isset($GLOBALS['companyMembers'][$data->member_id])) {
                    $memberText = $GLOBALS['companyMembers'][$data->member_id];
                } else {
                    $memberText .= 'Сотрудник удален';
                }
            } catch (\Exception $e) {
                $memberText = '-';
            }

        }

        return $memberText;

    }

                },
            ],
            [
                'attribute' => 'author_id',
                'options' => [
                    'style' => 'width: 120px',
                ],
                'value' => function ($data) {
    if($data->author_id == 0) {
        return '-';
    } else {
        return $GLOBALS['authorMembers'][$data->author_id];
    }
                },
            ],
            [
                'attribute' => 'type',
                'options' => [
                    'style' => 'width: 100px',
                ],
                'value' => function ($data) {

        if($data->type == -1) {
            return '-';
        } else {
            return $GLOBALS['types'][$data->type];
        }

                },
            ],
            [
                'header' => 'Вложения',
                'format' => 'raw',
                'options' => [
                    'style' => 'width: 220px',
                ],
                'value' => function ($data) {

                    $pathfolder = \Yii::getAlias('@webroot/files/attaches/' . $data->company_id . '/');
                    $shortPath = '/files/attaches/' . $data->company_id . '/';

                    if (file_exists($pathfolder)) {

                        $numFiles = 0;
                        $resLinksFiles = '';
                        $arrStateID = [];

                        foreach (FileHelper::findFiles($pathfolder) as $file) {

                            $arrStateID = explode('-', basename($file));

                            if(is_numeric($arrStateID[0])) {
                                if ($arrStateID[0] == $data->id) {
                                    $resLinksFiles .= Html::a(str_replace($data->id . '-', '', basename($file)), $shortPath . basename($file), ['target' => '_blank']) . '<br />';
                                    $numFiles++;
                                }
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
    echo "<div id='fullComment' style='word-wrap: break-word;'></div>";
    Modal::end();

    // Модальное окно показать все вложения
    $pathfolder = \Yii::getAlias('@webroot/files/attaches/' . $model->id . '/');
    $shortPath = '/files/attaches/' . $model->id . '/';

    $numFiles = 0;
    $resLinksFiles = '';

    if (file_exists($pathfolder)) {

        $arrFilesList = [];

        // заполняем массив
        foreach (FileHelper::findFiles($pathfolder) as $file) {
            if((basename($file) != 'attaches.zip') && (basename($file) != '.DS_Store')) {
                $time_file = filemtime($pathfolder . basename($file));

                $arrFilesList[$numFiles][0] = $time_file;
                $arrFilesList[$numFiles][1] = date('H:i d.m.Y', $time_file);
                $arrFilesList[$numFiles][2] = basename($file);

                $numFiles++;
            }
        }

        // сортируем массив
        for($iFiles = 0; $iFiles < $numFiles; $iFiles++) {

            $min_i = $iFiles;
            $min = $arrFilesList[$iFiles][0];

            for($jFiles = $iFiles + 1; $jFiles < $numFiles; $jFiles++) {

                if($arrFilesList[$jFiles][0] < $min) {
                    $min_i = $jFiles;
                    $min = $arrFilesList[$jFiles][0];
                }

            }

            if($iFiles != $min_i) {
                $tmpFIle[0] = $arrFilesList[$iFiles][0];
                $tmpFIle[1] = $arrFilesList[$iFiles][1];
                $tmpFIle[2] = $arrFilesList[$iFiles][2];

                $arrFilesList[$iFiles][0] = $arrFilesList[$min_i][0];
                $arrFilesList[$iFiles][1] = $arrFilesList[$min_i][1];
                $arrFilesList[$iFiles][2] = $arrFilesList[$min_i][2];

                $arrFilesList[$min_i][0] = $tmpFIle[0];
                $arrFilesList[$min_i][1] = $tmpFIle[1];
                $arrFilesList[$min_i][2] = $tmpFIle[2];

            }

        }

        // выводим полный список файлов
        for($iFiles = 0; $iFiles < $numFiles; $iFiles++) {

            $arrStateID = explode('-', $arrFilesList[$iFiles][2]);

            $time_file = filemtime($pathfolder . $arrFilesList[$iFiles][2]);

            if(is_numeric($arrStateID[0])) {
                $resLinksFiles .= '<span class="attachDate" style="color:#757575; margin-right:10px;">' . date('H:i d.m.Y', $time_file) . '</span>' . Html::a(str_replace($arrStateID[0] . '-', '', $arrFilesList[$iFiles][2]), $shortPath . $arrFilesList[$iFiles][2], ['target' => '_blank']) . '<br />';
            } else {
                $resLinksFiles .= '<span class="attachDate" style="color:#757575; margin-right:10px;">' . date('H:i d.m.Y', $time_file) . '</span>' . Html::a($arrFilesList[$iFiles][2], $shortPath . $arrFilesList[$iFiles][2], ['target' => '_blank']) . '<br />';
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

    // Модальное окно добавить вложения
    $pathfolder = \Yii::getAlias('@webroot/files/attaches/' . $model->id . '/');
    $shortPath = '/files/attaches/' . $model->id . '/';

    $modalAttach = Modal::begin([
        'header' => '<h5>Добавить вложения</h5>',
        'id' => 'showFormAttach',
        'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
        'size'=>'modal-lg',
    ]);

    echo "<div style='font-size: 15px; margin-left:15px;'>Выберите файлы:</div>";

    $modelAddAttach = new DynamicModel(['files']);
    $modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

    $form = ActiveForm::begin([
        'action' => ['/company/newattach', 'id' => $model->id],
        'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
        'fieldConfig' => [
            'template' => '<div class="col-sm-6">{input}</div>',
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]);

    echo $form->field($modelAddAttach, 'files[]')->fileInput(['multiple' => true]);

    echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']);

    ActiveForm::end();

    Modal::end();
    // Модальное окно добавить вложения

    ?>
    </div>
</div>
