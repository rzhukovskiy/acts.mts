<?php
use common\models\Service;
use yii\bootstrap\Tabs;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $searchModel \common\models\search\ActSearch
 */

$this->title = 'Выгрузка актов';

$request = Yii::$app->request;

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

        $iA = 0;
        $idD = 0;

        foreach (FileHelper::findFiles($path) as $file) {

            $fileName = basename($file);
            $fileName = str_replace('__', '_', $fileName);

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
                $tmpStrint = str_replace('_', ' ', $tmpStrint) . ' ' . $pref;

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][7] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'оп._дезинфекция_Счет_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'доп._дезинфекция_Счет_') + 39));

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
                $tmpStrint = str_replace('_', ' ', $tmpStrint) . ' ' . $pref;

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrDopListFiles[str_replace(' ', '_', $tmpStrint)][8] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'оп._дезинфекция_Акт_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'доп._дезинфекция_Акт_') + 37));

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
                $tmpStrint = str_replace('_', ' ', $tmpStrint) . ' ' . $pref;

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
                $tmpStrint = str_replace('_', ' ', $tmpStrint) . ' ' . $pref;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][4] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'езинфекция_Счет_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'дезинфекция_Счет_') + 32));

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
                $tmpStrint = str_replace('_', ' ', $tmpStrint) . ' ' . $pref;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][5] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'езинфекция_Акт_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'дезинфекция_Акт_') + 30));

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
                $tmpStrint = str_replace('_', ' ', $tmpStrint) . ' ' . $pref;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][6] = $file;
                $tmpStrint = '';

                $iA++;
                $idD++;

            } else if (strpos($fileName, 'кт_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Акт_') + 7));
                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][1] = $file;
                $tmpStrint = '';

                $iA++;
            } else if (strpos($fileName, 'татистика_анализ_мо') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Статистика_анализ_мо') + 47));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][2] = $file;
                $tmpStrint = '';

                $iA++;

            } else if (strpos($fileName, 'татистика_анализ_сервис_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Статистика_анализ_сервис_') + 47));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][2] = $file;
                $tmpStrint = '';

                $iA++;

            } else if (strpos($fileName, 'татистика_анализ_шиномонтаж_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Статистика_анализ_шиномонтаж_') + 55));

                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][2] = $file;
                $tmpStrint = '';

                $iA++;

            } else if (strpos($fileName, 'чет_') > 0) {

                $tmpStrint = substr($fileName, (strpos($fileName, 'Счет_') + 9));
                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][0] = $tmpStrint;

                $arrListFiles[str_replace(' ', '_', $tmpStrint)][3] = $file;
                $tmpStrint = '';

                $iA++;

            }

        }

        if($iA > 0) {

            $i = 0;
            header ("Content-Type: text/html; charset=utf-8");
            $echoFiles = '<div class="form-group grid-view"><div class="col-sm-12"><table border="1" bordercolor="#dddddd"><tr style="background: #428bca; color: #fff;"><td width="100px" style="padding-left: 10px;">№</td><td width="auto" style="padding: 3px 0px 3px 10px">Название организации</td><td width="80px" style="padding-left: 10px;">Файл</td></tr>';

            foreach ($arrListFiles as $key => $value) {

                $echoFiles .= '<tr><td width="100px" valign="top" style="padding: 10px;">' . ($i + 1) . '</td><td width="auto" style="padding:10px;"><table>';

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
                    $echoFiles .= '<tr><td>Дезинфекция справка</td></tr>';
                }

                if (isset($arrListFiles[$key][5])) {
                    $echoFiles .= '<tr><td>Дезинфекция счет</td></tr>';
                }

                if (isset($arrListFiles[$key][6])) {
                    $echoFiles .= '<tr><td>Дезинфекция акт</td></tr>';
                }

                if (isset($arrListFiles[$key][7])) {
                    $echoFiles .= '<tr><td>Доп. дезинфекция справка</td></tr>';
                }

                if (isset($arrListFiles[$key][8])) {
                    $echoFiles .= '<tr><td>Доп. дезинфекция счет</td></tr>';
                }

                if (isset($arrListFiles[$key][9])) {
                    $echoFiles .= '<tr><td>Доп. дезинфекция акт</td></tr>';
                }

                $echoFiles .= '</table></td><td width="80px" style="padding:10px;"><table style="margin-top:19px;">';

                if (isset($arrListFiles[$key][0])) {

                    $echoFiles .= '<tr><td></td></tr>';

                }

                if (isset($arrListFiles[$key][1])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][1]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][3])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][3]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][2])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][2]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][4])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][4]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][5])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][5]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][6])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][6]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][7])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][7]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][8])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][8]) . '</td></tr>';
                }

                if (isset($arrListFiles[$key][9])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$key][9]) . '</td></tr>';
                }

                $echoFiles .= '</table></td></tr>';

                $i++;

            }

            $echoFiles .= '</table></div></div>';

            echo $echoFiles;

            if($idD > 0) {
                $i = 0;

                $echoFiles = '<div class="form-group grid-view"><div class="col-sm-12" style="margin-top: 20px;"><table border="1" bordercolor="#dddddd"><tr style="background: #428bca; color: #fff;"><td align="center" colspan="3" style="padding: 3px 0px 3px 0px">Доп. дезинфекция</td></tr><tr style="background: #eff6fc; color: #3079b5;"><td width="100px" style="padding-left: 10px;">№</td><td width="auto" style="padding: 3px 0px 3px 10px">Название организации</td><td width="80px" style="padding-left: 10px;">Файл</td></tr>';

                foreach ($arrDopListFiles as $key => $value) {

                    $echoFiles .= '<tr><td width="100px" valign="top" style="padding: 10px;">' . ($i + 1) . '</td><td width="auto" style="padding:10px;"><table>';

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
                        $echoFiles .= '<tr><td>Дезинфекция справка</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][5])) {
                        $echoFiles .= '<tr><td>Дезинфекция счет</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][6])) {
                        $echoFiles .= '<tr><td>Дезинфекция акт</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][7])) {
                        $echoFiles .= '<tr><td>Доп. дезинфекция справка</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][8])) {
                        $echoFiles .= '<tr><td>Доп. дезинфекция счет</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][9])) {
                        $echoFiles .= '<tr><td>Доп. дезинфекция акт</td></tr>';
                    }

                    $echoFiles .= '</table></td><td width="80px" style="padding:10px;"><table style="margin-top:19px;">';

                    if (isset($arrDopListFiles[$key][0])) {

                        $echoFiles .= '<tr><td></td></tr>';

                    }

                    if (isset($arrDopListFiles[$key][1])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][1]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][3])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][3]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][2])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][2]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][4])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][4]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][5])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][5]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][6])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][6]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][7])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][7]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][8])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][8]) . '</td></tr>';
                    }

                    if (isset($arrDopListFiles[$key][9])) {
                        $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrDopListFiles[$key][9]) . '</td></tr>';
                    }

                    $echoFiles .= '</table></td></tr>';

                    $i++;

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
