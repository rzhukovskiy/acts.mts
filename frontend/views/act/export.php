<?php
use common\models\Service;
use yii\bootstrap\Tabs;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\ActExport;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $searchModel \common\models\search\ActSearch
 */

$this->title = 'Выгрузка актов';

$request = Yii::$app->request;

$actionLink = Url::to('@web/act/exportsave');

$script = <<< JS

    // Записываем в базу когда скачивался акт
    
    $(".loadAct").on('click', '*', function() {
        var clickLink = $(this).attr('href');
        
        var type = $type;
        var company = '';
        
        var splitLink = clickLink.split("/");
        
        var dataExpl = splitLink[5];
        var name = splitLink[6];
        
        if(clickLink.lastIndexOf('client') > 0) {
            company = 1;
        } else {
            company = 0;
        }
        
        // Меняем статус на скачан
        var statusFind = $('.statusLoad[data-name="' + $(this).parent().attr("data-name") + '"]');
        statusFind.text('Скачан');
        statusFind.css('color', '#3fad46');
        // Меняем статус на скачан
        
        $.ajax({
                type     :'POST',
                cache    : false,
                data:'type=' + type + '&company=' + company + '&dataExpl=' + dataExpl + '&name=' + name,
                url  : '$actionLink',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                } else {
                // Неудачно
                }
                
                }
                });
        
    });

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Акты',
            'url' => $request->referrer,
            'active' => false,
        ],
        [
            'label' => 'Выгрузка',
            'url' => '#',
            'active' => true,
        ],
    ],
]);

$typeInt = $type;
$type = Service::$listType[$type]['en'];
$time = \DateTime::createFromFormat('m-Y-d', $searchModel->period . '-01')->getTimestamp();
$path = "files/acts/" . ($company ? 'client' : 'partner') . "/$type/" . date('m-Y', $time);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Файлы:
    </div>
    <div class="panel-body" style="padding: 50px">

        <?php

        $arrListFiles = [];
        $arrDopListFiles = [];
        $arrStatusFile = [];

        $iA = 0;
        $idD = 0;
        $arrSpravki = [];
        $arrDopSpravki = [];

        foreach (FileHelper::findFiles($path) as $file) {

            $fileName = basename($file);
            $fileName = mb_convert_encoding($fileName, 'utf-8', mb_detect_encoding($fileName));
            $fileName = str_replace('__', '_', $fileName);

            $file_name_search = basename($file);
            $file_name_search = mb_convert_encoding($file_name_search, 'utf-8', mb_detect_encoding($file_name_search));
            $statusFile = 0;

            if (strpos($fileName, 'оп._дезинфекция_Справка_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'доп._дезинфекция_Справка_') + 45));

                $pref = '';
                if(($fileName[strlen($fileName) - 11] . $fileName[strlen($fileName) - 10]) == '20') {
                    if($fileName[strlen($fileName) - 7] == '-') {
                        $pref = $fileName[strlen($fileName) - 6];
                    } else {
                        $pref = $fileName[strlen($fileName) - 7] . $fileName[strlen($fileName) - 6];
                    }
                } else if(($fileName[strlen($fileName) - 12] . $fileName[strlen($fileName) - 11]) == '20') {
                    $pref = $fileName[strlen($fileName) - 7] . $fileName[strlen($fileName) - 6];
                }

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][1] = $statusFile;
                // Проверяем статус файла

                if($pref == '') {
                    $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;
                    $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][7] = $file;
                } else {

                    $premfp =  '';

                    if(strpos($tmpStrint, 'МФП 1') > 0) {
                        $tmpStrint = " ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 1";
                    } else if(strpos($tmpStrint, 'МФП 2') > 0) {
                        $tmpStrint = " ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 2";
                    } else if(strpos($tmpStrint, 'МФП 3') > 0) {
                        $tmpStrint = " ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 3";
                    } else if(strpos($tmpStrint, 'МФП 4') > 0) {
                        $tmpStrint = " ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 4";
                    } else if(strpos($tmpStrint, 'МФП 5') > 0) {
                        $tmpStrint = " ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 5";
                    } else {
                        $premfp = 'Справка №' . $pref;
                    }

                    $arrDopSpravki[$idD][0] = $premfp;
                    $arrDopSpravki[$idD][1] = $file;

                    $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                    if(isset($arrDopListFiles[str_replace(' ', '_', $tmpStrint)][10])) {
                        $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][10] .= $idD . "-";
                    } else {
                        $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][10] = $idD . "-";
                    }

                }

                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'оп._дезинфекция_Счет_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'доп._дезинфекция_Счет_') + 39));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][1] = $statusFile;
                // Проверяем статус файла

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][8] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'оп._дезинфекция_Акт_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'доп._дезинфекция_Акт_') + 37));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][1] = $statusFile;
                // Проверяем статус файла

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][9] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'езинфекция_Справка_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'дезинфекция_Справка_') + 38));

                $pref = '';
                if(($fileName[strlen($fileName) - 11] . $fileName[strlen($fileName) - 10]) == '20') {
                    if($fileName[strlen($fileName) - 7] == '-') {
                        $pref = $fileName[strlen($fileName) - 6];
                    } else {
                        $pref = $fileName[strlen($fileName) - 7] . $fileName[strlen($fileName) - 6];
                    }
                } else if(($fileName[strlen($fileName) - 12] . $fileName[strlen($fileName) - 11]) == '20') {
                    $pref = $fileName[strlen($fileName) - 7] . $fileName[strlen($fileName) - 6];
                }

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                if($pref == '') {
                    $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;
                    $arrListFiles[str_replace(' ', '_', $tmpStrint)][4] = $file;
                } else {

                    $premfp =  '';

                    if(strpos($tmpStrint, 'МФП 1') > 0) {
                        $tmpStrint = "ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 1";
                    } else if(strpos($tmpStrint, 'МФП 2') > 0) {
                        $tmpStrint = "ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 2";
                    } else if(strpos($tmpStrint, 'МФП 3') > 0) {
                        $tmpStrint = "ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 3";
                    } else if(strpos($tmpStrint, 'МФП 4') > 0) {
                        $tmpStrint = "ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 4";
                    } else if(strpos($tmpStrint, 'МФП 5') > 0) {
                        $tmpStrint = "ООО Агро-Авто (Москва ЮГ - МФП)";
                        $premfp = "Справка №$pref МФП 5";
                    } else {
                        $premfp = 'Справка №' . $pref;
                    }

                    $arrSpravki[$idD][0] = $premfp;
                    $arrSpravki[$idD][1] = $file;

                    $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                    if(isset($arrListFiles[str_replace(' ', '_', $tmpStrint)][11])) {
                        $arrListFiles[str_replace(' ', '_', $tmpStrint)][11] .= $idD . "-";
                    } else {
                        $arrListFiles[str_replace(' ', '_', $tmpStrint)][11] = $idD . "-";
                    }

                }

                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'езинфекция_Счет_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'дезинфекция_Счет_') + 32));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][5] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'езинфекция_Акт_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'дезинфекция_Акт_') + 30));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][6] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'кт_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Акт_') + 7));
                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][1] = $file;
                $tmpStrint = '';

                $iA++;
            } else if (strpos($fileName, 'татистика_анализ_мо') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Статистика_анализ_мо') + 45));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][2] = $file;
                $tmpStrint = '';

                $iA++;

            } else if (strpos($fileName, 'татистика_анализ_сервис_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Статистика_анализ_сервис_') + 47));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][2] = $file;
                $tmpStrint = '';

                $iA++;

            } else if (strpos($fileName, 'татистика_анализ_шиномонтаж_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Статистика_анализ_шиномонтаж_') + 55));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][2] = $file;
                $tmpStrint = '';

                $iA++;

            } else if (strpos($fileName, 'чет_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Счет_') + 9));
                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                // Проверяем статус файла

                $dataExpl = '';

                if (isset(Yii::$app->request->get('ActSearch')['period'])) {
                    $dataExpl = (string)Yii::$app->request->get('ActSearch')['period'];

                    $dataExplArr = explode('-', $dataExpl);

                    if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                        $dataExpl = 0 . $dataExpl;
                    }

                } else {
                    $dataExpl = date('m-Y');
                }

                $resActLoad = ActExport::find()->where(['type' => $typeInt, 'company' => $company, 'period' => $dataExpl, 'name' => $file_name_search])->select('id')->column();

                if (count($resActLoad) > 0) {
                    if (isset($resActLoad[0])) {
                        $statusFile = 1;
                    }
                }

                $arrStatusFile[str_replace(' ', '_', $tmpStrint)][0] = $statusFile;
                // Проверяем статус файла

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][3] = $file;
                $tmpStrint = '';

                $iA++;

            }

            $file_name_search = '';
            $statusFile = 0;

        }

        if($iA > 0) {

            $iz = 0;
            $echoFiles = '<div class="form-group grid-view"><div class="col-sm-12"><table border="1" bordercolor="#dddddd"><tr style="background: #428bca; color: #fff;"><td align="center" colspan="4" style="padding: 3px 0px 3px 0px">';

            switch (Yii::$app->request->get('type')) {
                case 2:
                    $echoFiles .= 'Мойка';
                    break;
                case 3:
                    $echoFiles .= 'Сервис';
                    break;
                case 4:
                    $echoFiles .= 'Шиномонтаж';
                    break;
                case 5:
                    $echoFiles .= 'Дезинфекция';
                    break;
            }

            $echoFiles .= '</td></tr><tr style="background: #eff6fc; color: #3079b5;"><td width="100px" style="padding-left: 10px;">№</td><td width="700px" style="padding: 3px 0px 3px 10px">Название организации</td><td width="80px" style="padding-left: 10px;">Файл</td><td width="110px" style="padding-left: 10px;">Статус</td></tr>';

            foreach ($arrListFiles as $key => $value) {

                $echoFiles .= '<tr><td width="100px" valign="top" style="padding: 10px;">' . ($iz + 1) . '</td><td width="700px" style="padding:10px;"><table>';

                if (isset($arrListFiles[$key][0])) {

                    $echoFiles .= '<tr><td><b>' . $arrListFiles[$key][0] . '</b></td></tr>';

                }

                if (isset($arrListFiles[$key][1])) {
                    $echoFiles .= '<tr><td>Акт</td></tr>';
                }

                if (isset($arrListFiles[$key][3])) {
                    $echoFiles .= '<tr><td>Счет</td></tr>';
                }

                if (isset($arrListFiles[$key][2])) {
                    $echoFiles .= '<tr><td>Анализ</td></tr>';
                }

                if (isset($arrListFiles[$key][4])) {
                    $echoFiles .= '<tr><td>Справка</td></tr>';
                }

                if (isset($arrListFiles[$key][5])) {
                    $echoFiles .= '<tr><td>Счет</td></tr>';
                }

                if (isset($arrListFiles[$key][6])) {
                    $echoFiles .= '<tr><td>Акт</td></tr>';
                }

                if (isset($arrListFiles[$key][7])) {
                    $echoFiles .= '<tr><td>Справка</td></tr>';
                }

                if (isset($arrListFiles[$key][8])) {
                    $echoFiles .= '<tr><td>Счет</td></tr>';
                }

                if (isset($arrListFiles[$key][9])) {
                    $echoFiles .= '<tr><td>Акт</td></tr>';
                }

                if (isset($arrListFiles[$key][10])) {
                    $tmpArrNames = explode('-', $arrListFiles[$key][10]);

                    for ($i = 0; $i < count($tmpArrNames); $i++) {
                        if(isset($arrSpravki[$tmpArrNames[$i]][0])) {
                            $echoFiles .= '<tr><td>' . $arrSpravki[$tmpArrNames[$i]][0] . '</td></tr>';
                        }
                    }

                }

                if (isset($arrListFiles[$key][11])) {
                    $tmpArrNames = explode('-', $arrListFiles[$key][11]);

                    for ($i = 0; $i < count($tmpArrNames); $i++) {
                        if(isset($arrSpravki[$tmpArrNames[$i]][0])) {
                            $echoFiles .= '<tr><td>' . $arrSpravki[$tmpArrNames[$i]][0] . '</td></tr>';
                        }
                    }

                }

                $echoFiles .= '</table></td><td width="80px" style="padding:10px;"><table style="margin-top:19px;">';

                if (isset($arrListFiles[$key][0])) {

                    $echoFiles .= '<tr><td></td></tr>';

                }

                if (isset($arrListFiles[$key][1])) {

                    $getFileName = explode('/' , $arrListFiles[$key][1]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][1]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][3])) {

                    $getFileName = explode('/' , $arrListFiles[$key][3]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][3]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][2])) {

                    $getFileName = explode('/' , $arrListFiles[$key][2]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][2]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][4])) {

                    $getFileName = explode('/' , $arrListFiles[$key][4]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][4]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][5])) {

                    $getFileName = explode('/' , $arrListFiles[$key][5]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][5]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][6])) {

                    $getFileName = explode('/' , $arrListFiles[$key][6]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][6]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][7])) {

                    $getFileName = explode('/' , $arrListFiles[$key][7]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][7]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][8])) {

                    $getFileName = explode('/' , $arrListFiles[$key][8]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][8]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][9])) {

                    $getFileName = explode('/' , $arrListFiles[$key][9]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrListFiles[$key][9]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][10])) {
                    $tmpArrNames = explode('-', $arrListFiles[$key][10]);

                    for ($i = 0; $i < count($tmpArrNames); $i++) {
                        if(isset($arrSpravki[$tmpArrNames[$i]][1])) {

                            $getFileName = explode('/' , $arrSpravki[$tmpArrNames[$i]][1]);
                            $getFileName = $getFileName[(count($getFileName) - 1)];
                            
                            $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrSpravki[$tmpArrNames[$i]][1]) . '</td></tr>';
                        }
                    }

                }

                if (isset($arrListFiles[$key][11])) {
                    $tmpArrNames = explode('-', $arrListFiles[$key][11]);

                    for ($i = 0; $i < count($tmpArrNames); $i++) {
                        if(isset($arrSpravki[$tmpArrNames[$i]][1])) {

                            $getFileName = explode('/' , $arrSpravki[$tmpArrNames[$i]][1]);
                            $getFileName = $getFileName[(count($getFileName) - 1)];
                            
                            $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrSpravki[$tmpArrNames[$i]][1]) . '</td></tr>';
                        }
                    }

                }

                $echoFiles .= '</table></td><td width="110px" style="padding:10px;"><table style="margin-top:19px;">';

                if (isset($arrListFiles[$key][0])) {

                    $echoFiles .= '<tr><td></td></tr>';

                }

                if (isset($arrListFiles[$key][1])) {

                    $getFileName = explode('/' , $arrListFiles[$key][1]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][3])) {

                    $getFileName = explode('/' , $arrListFiles[$key][3]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][2])) {

                    $getFileName = explode('/' , $arrListFiles[$key][2]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][4])) {

                    $getFileName = explode('/' , $arrListFiles[$key][4]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][5])) {

                    $getFileName = explode('/' , $arrListFiles[$key][5]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][6])) {

                    $getFileName = explode('/' , $arrListFiles[$key][6]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][7])) {

                    $getFileName = explode('/' , $arrListFiles[$key][7]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][8])) {

                    $getFileName = explode('/' , $arrListFiles[$key][8]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][9])) {

                    $getFileName = explode('/' , $arrListFiles[$key][9]);
                    $getFileName = $getFileName[(count($getFileName) - 1)];
                    
                    $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                }

                if (isset($arrListFiles[$key][10])) {
                    $tmpArrNames = explode('-', $arrListFiles[$key][10]);

                    for ($i = 0; $i < count($tmpArrNames); $i++) {
                        if(isset($arrSpravki[$tmpArrNames[$i]][1])) {

                            $getFileName = explode('/' , $arrSpravki[$tmpArrNames[$i]][1]);
                            $getFileName = $getFileName[(count($getFileName) - 1)];
                            
                            $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                        }
                    }

                }

                if (isset($arrListFiles[$key][11])) {
                    $tmpArrNames = explode('-', $arrListFiles[$key][11]);

                    for ($i = 0; $i < count($tmpArrNames); $i++) {
                        if(isset($arrSpravki[$tmpArrNames[$i]][1])) {

                            $getFileName = explode('/' , $arrSpravki[$tmpArrNames[$i]][1]);
                            $getFileName = $getFileName[(count($getFileName) - 1)];
                            
                            $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][0] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][0] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                        }
                    }

                }

                $echoFiles .= '</table></td></tr>';

                $iz++;

            }

            $echoFiles .= '</table></div></div>';

            echo $echoFiles;

            if($idD > 0) {
                $iz = 0;

                $echoFiles = '<div class="form-group grid-view"><div class="col-sm-12" style="margin-top: 20px;"><table border="1" bordercolor="#dddddd"><tr style="background: #428bca; color: #fff;"><td align="center" colspan="4" style="padding: 3px 0px 3px 0px">Доп. дезинфекция</td></tr><tr style="background: #eff6fc; color: #3079b5;"><td width="100px" style="padding-left: 10px;">№</td><td width="700px" style="padding: 3px 0px 3px 10px">Название организации</td><td width="80px" style="padding-left: 10px;">Файл</td><td width="110px" style="padding-left: 10px;">Статус</td></tr>';

                foreach ($arrDopListFiles as $key => $value) {

                    $echoFiles .= '<tr><td width="100px" valign="top" style="padding: 10px;">' . ($iz + 1) . '</td><td width="700px" style="padding:10px;"><table>';

                    if (isset($arrDopListFiles[$key][0])) {

                        $echoFiles .= '<tr><td><b>' . $arrDopListFiles[$key][0] . '</b></td></tr>';

                    }

                    if (isset($arrDopListFiles[$key][1])) {
                        $echoFiles .= '<tr><td>Акт</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][3])) {
                        $echoFiles .= '<tr><td>Счет</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][2])) {
                        $echoFiles .= '<tr><td>Анализ</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][4])) {
                        $echoFiles .= '<tr><td>Справка</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][5])) {
                        $echoFiles .= '<tr><td>Счет</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][6])) {
                        $echoFiles .= '<tr><td>Акт</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][7])) {
                        $echoFiles .= '<tr><td>Справка</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][8])) {
                        $echoFiles .= '<tr><td>Счет</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][9])) {
                        $echoFiles .= '<tr><td>Акт</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][10])) {
                        $tmpArrNames = explode('-', $arrDopListFiles[$key][10]);

                        for ($i = 0; $i < count($tmpArrNames); $i++) {
                            if(isset($arrDopSpravki[$tmpArrNames[$i]][0])) {
                                $echoFiles .= '<tr><td>' . $arrDopSpravki[$tmpArrNames[$i]][0] . '</td></tr>';
                            }
                        }

                    }

                    if (isset($arrDopListFiles[$key][11])) {
                        $tmpArrNames = explode('-', $arrDopListFiles[$key][11]);

                        for ($i = 0; $i < count($tmpArrNames); $i++) {
                            if(isset($arrDopSpravki[$tmpArrNames[$i]][0])) {
                                $echoFiles .= '<tr><td>' . $arrDopSpravki[$tmpArrNames[$i]][0] . '</td></tr>';
                            }
                        }

                    }

                    $echoFiles .= '</table></td><td width="80px" style="padding:10px;"><table style="margin-top:19px;">';

                    if (isset($arrDopListFiles[$key][0])) {

                        $echoFiles .= '<tr><td></td></tr>';

                    }

                    if (isset($arrDopListFiles[$key][1])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][1]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][1]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][3])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][3]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][3]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][2])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][2]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][2]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][4])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][4]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][4]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][5])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][5]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][5]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][6])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][6]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][6]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][7])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][7]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][7]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][8])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][8]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][8]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][9])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][9]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopListFiles[$key][9]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][10])) {
                        $tmpArrNames = explode('-', $arrDopListFiles[$key][10]);

                        for ($i = 0; $i < count($tmpArrNames); $i++) {
                            if(isset($arrDopSpravki[$tmpArrNames[$i]][1])) {

                                $getFileName = explode('/' , $arrDopSpravki[$tmpArrNames[$i]][1]);
                                $getFileName = $getFileName[(count($getFileName) - 1)];

                                $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopSpravki[$tmpArrNames[$i]][1]) . '</td></tr>';
                            }
                        }

                    }

                    if (isset($arrDopListFiles[$key][11])) {
                        $tmpArrNames = explode('-', $arrDopListFiles[$key][11]);

                        for ($i = 0; $i < count($tmpArrNames); $i++) {
                            if(isset($arrDopSpravki[$tmpArrNames[$i]][1])) {

                                $getFileName = explode('/' , $arrDopSpravki[$tmpArrNames[$i]][1]);
                                $getFileName = $getFileName[(count($getFileName) - 1)];

                                $echoFiles .= '<tr><td class="loadAct" data-name="' . $getFileName . '">' . Html::a('Скачать', '/' . $arrDopSpravki[$tmpArrNames[$i]][1]) . '</td></tr>';
                            }
                        }

                    }

                    $echoFiles .= '</table></td><td width="110px" style="padding:10px;"><table style="margin-top:19px;">';

                    if (isset($arrDopListFiles[$key][0])) {

                        $echoFiles .= '<tr><td></td></tr>';

                    }

                    if (isset($arrDopListFiles[$key][1])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][1]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][3])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][3]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][2])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][2]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][4])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][4]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][5])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][5]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][6])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][6]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][7])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][7]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][8])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][8]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][9])) {

                        $getFileName = explode('/' , $arrDopListFiles[$key][9]);
                        $getFileName = $getFileName[(count($getFileName) - 1)];

                        $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][10])) {
                        $tmpArrNames = explode('-', $arrDopListFiles[$key][10]);

                        for ($i = 0; $i < count($tmpArrNames); $i++) {
                            if(isset($arrDopSpravki[$tmpArrNames[$i]][1])) {

                                $getFileName = explode('/' , $arrDopSpravki[$tmpArrNames[$i]][1]);
                                $getFileName = $getFileName[(count($getFileName) - 1)];

                                $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                            }
                        }

                    }

                    if (isset($arrDopListFiles[$key][11])) {
                        $tmpArrNames = explode('-', $arrDopListFiles[$key][11]);

                        for ($i = 0; $i < count($tmpArrNames); $i++) {
                            if(isset($arrDopSpravki[$tmpArrNames[$i]][1])) {

                                $getFileName = explode('/' , $arrDopSpravki[$tmpArrNames[$i]][1]);
                                $getFileName = $getFileName[(count($getFileName) - 1)];

                                $echoFiles .= '<tr><td class="statusLoad" data-name="' . $getFileName . '" style="color:#' . (($arrStatusFile[$key][1] == 1) ? '3fad46' : 'd9534f') . '">' . (($arrStatusFile[$key][1] == 1) ? 'Скачан' : 'Не скачан') . '</td></tr>';
                            }
                        }

                    }

                    $echoFiles .= '</table></td></tr>';

                    $iz++;

                }

                $echoFiles .= '</table></div></div>';

                echo $echoFiles;
            }

        }

        /*foreach (FileHelper::findFiles($path) as $file) {
            echo '<div class="form-group grid-view"><div class="col-sm-12">' . Html::a(basename($file), '/' . $file) . '</div></div>';
        }*/

        /*        foreach (FileHelper::findFiles($path) as $file) {
                    <div class="form-group grid-view">
                        <div class="col-sm-12">
                            echo Html::a(basename($file), '/' . $file) ;
                        </div>
                    </div>
                }*/

        ?>

    </div>
</div>