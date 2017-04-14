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

        $iA = 0;
        $iS = 0;
        $iAS = 0;
        $arrListFiles = [];

        foreach (FileHelper::findFiles($path) as $file) {

            if(strpos(basename($file), 'Акт_') > 0) {

                $fileName = basename($file);
                $fileName = str_replace('__', '_', $fileName);

                $tmpStrint = substr($fileName, (strpos($fileName, 'Акт_') + 7));
                $tmpStrint = substr($tmpStrint, 0, ((strpos($tmpStrint, '_от'))));
                $tmpStrint = str_replace('_', ' ', $tmpStrint);

                $arrListFiles[$iA][0] = $tmpStrint;
                $tmpStrint = '';

                $arrListFiles[$iA][1] = $file;
                $iA++;
            } else if(strpos(basename($file), 'Статистика_анализ_') > 0) {
                $arrListFiles[$iAS][2] = $file;
                $iAS++;
            } else {
                $arrListFiles[$iS][3] = $file;
                $iS++;
            }

        }

        if($iA > 0) {

            $echoFiles = '<div class="form-group grid-view"><div class="col-sm-12" style="margin-bottom: 10px;"><table border="1" bordercolor="#dddddd"><tr style="background: #428bca; color: #fff;"><td width="100px" style="padding-left: 10px;">№</td><td width="auto" style="padding: 3px 0px 3px 10px">Название организации</td><td width="80px" style="padding-left: 10px;">Файл</td></tr>';

            for ($i = 0; $i < $iA; $i++) {

                $echoFiles .= '<tr><td width="100px" valign="top" style="padding: 10px;">' . ($i + 1) . '</td><td width="auto" style="padding:10px;"><table>';

                if (isset($arrListFiles[$i][0])) {

                    $echoFiles .= '<tr><td><b>' . $arrListFiles[$i][0] . '</b></td></tr>';

                }

                if (isset($arrListFiles[$i][1])) {
                    $echoFiles .= '<tr><td>Акт</td></tr>';
                }

                if (isset($arrListFiles[$i][3])) {
                    $echoFiles .= '<tr><td>Счет</td></tr>';
                }

                if (isset($arrListFiles[$i][2])) {
                    $echoFiles .= '<tr><td>Анализ</td></tr>';
                }

                $echoFiles .= '</table></td><td width="80px" style="padding:10px;"><table style="margin-top:19px;">';

                if (isset($arrListFiles[$i][0])) {

                    $echoFiles .= '<tr><td></td></tr>';

                }

                if (isset($arrListFiles[$i][1])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$i][2]) . '</td></tr>';
                }

                if (isset($arrListFiles[$i][3])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$i][2]) . '</td></tr>';
                }

                if (isset($arrListFiles[$i][2])) {
                    $echoFiles .= '<tr><td>' . Html::a('Скачать', '/' . $arrListFiles[$i][2]) . '</td></tr>';
                }

                $echoFiles .= '</table></td></tr>';

            }

            $echoFiles .= '</table></div></div>';

            echo $echoFiles;

        }

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
