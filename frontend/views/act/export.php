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
$path = "files/acts/$type/" . date('m-Y', $time);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Файлы:
    </div>
    <div class="panel-body" style="padding: 50px">
        <?php foreach (FileHelper::findFiles($path) as $file) { ?>
            <div class="form-group grid-view">
                <div class="col-sm-12">
                    <?= Html::a(basename($file), '/' . $file) ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
