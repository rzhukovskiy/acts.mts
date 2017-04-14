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

        $disNote = true;

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

            if(strpos('_' . basename($file), 'дезинфекция') > 0) {
                $disNote = false;
                break;
            }

        }

        if($disNote) {

            for ($i = 0; $i < $iA; $i++) {

                if (isset($arrListFiles[$i][0])) {

                    if ($i == 0) {
                        echo '<div class="form-group grid-view"><div class="col-sm-12" style="margin-bottom: 10px;"><b>' . $arrListFiles[$i][0] . '</b></div></div>';
                    } else {
                        echo '<div class="form-group grid-view"><div class="col-sm-12" style="margin-bottom: 10px; margin-top: 25px;"><b>' . $arrListFiles[$i][0] . '</b></div></div>';
                    }

                }

                if (isset($arrListFiles[$i][1])) {
                    echo '<div class="form-group grid-view"><div class="col-sm-12">' . Html::a(basename($arrListFiles[$i][1]), '/' . $arrListFiles[$i][1]) . '</div></div>';
                }

                if (isset($arrListFiles[$i][3])) {
                    echo '<div class="form-group grid-view"><div class="col-sm-12">' . Html::a(basename($arrListFiles[$i][3]), '/' . $arrListFiles[$i][3]) . '</div></div>';
                }

                if (isset($arrListFiles[$i][2])) {
                    echo '<div class="form-group grid-view"><div class="col-sm-12">' . Html::a(basename($arrListFiles[$i][2]), '/' . $arrListFiles[$i][2]) . '</div></div>';
                }


            }
        } else {
            foreach (FileHelper::findFiles($path) as $file) {
                echo '<div class="form-group grid-view"><div class="col-sm-12">' . Html::a(basename($file), '/' . $file) . '</div></div>';
            }
        }

        ?>

    </div>
</div>
