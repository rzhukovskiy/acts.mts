<?php
/**
 * @var $model \common\models\Company
 * @var $entrySearchModel \common\models\search\EntrySearch
 */

use yii\bootstrap\Html;

?>
<div class="col-sm-4">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <?= $model->name ?>
        </div>
        <div class="panel-body">
            <div class="col-sm-6" style="margin-top: 15px;">
                <?= $model->info->address ?>
            </div>
            <div class="col-sm-6" style="margin-top: 15px;">
                <?= $model->info->start_at ? date('H:i', $model->info->start_at) : '00:00' ?> - <?= $model->info->end_at ? date('H:i', $model->info->end_at) : '23:00' ?>
            </div>
            <div class="free-time" style ="height: 220px; text-align: center;">
                <?php
                $arrayFreeTime = $model->getFreeTimeArray($entrySearchModel->day);
                foreach ($arrayFreeTime as $freeTime) {
                    echo '<div class="col-sm-12">' . $freeTime['start'] . ' - ' . $freeTime['end'] . '</div>';
                } ?>
            </div>
            <div class="col-sm-12 text-center">
                <?= Html::a('Записать на мойку', ['wash/view', 'id' => $model->id, 'Entry[day]' => $entrySearchModel->day], ['class' => 'btn btn-primary btn-sm pull-center', 'style' => 'margin-bottom: 20px']) ?>
            </div>
        </div>
    </div>
</div>