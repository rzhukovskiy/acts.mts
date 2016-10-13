<?php
/**
 * @var $model \common\models\Company
 * @var $entrySearchModel \common\models\search\EntrySearch
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>
<div class="col-sm-4">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <?= $model->name ?>
        </div>
        <div class="panel-body">
            <div class="col-sm-12" style="margin-top: 15px;">
                <?= $model->info->address ?>
            </div>
            <div class="col-sm-12" style="margin-top: 15px;">
                <?= date('H:i', $model->info->start_at) ?> - <?= date('H:i', $model->info->end_at) ?>
            </div>
            <div class="free-time" style ="height: 220px;">
                <?php
                $step = 0;
                $listEntry = $model->getFreeTimeArray(date('d-m-Y'));
                $timeStart = gmdate('H:i', $model->info->start_at);
                $timeEnd = gmdate('H:i', $model->info->end_at);
                foreach ($listEntry as $entry) {
                    if (!$step) {
                        if (date('H:i', $entry->start_at) != $timeStart) {
                            echo '<div class="col-sm-12">' . $timeStart . ' - ' . date('H:i', $entry->start_at) . '</div><div class="col-sm-12">';
                        } else {
                            echo '<div class="col-sm-12">';
                        }
                    } else {
                        echo date('H:i', $entry->start_at) . '</div><div class="col-sm-12">';
                    }
                    $step++;
                    if ($step == count($listEntry)) {
                        if (date('H:i', $entry->end_at) != $timeEnd) {
                            echo date('H:i', $entry->end_at) . ' - ' . $timeEnd . '</div>';
                        } else {
                            echo '</div>';
                        }
                    } else {
                        echo date('H:i', $entry->end_at) . ' - ';
                    }
                } ?>
            </div>
            <div class="col-sm-12 text-center">
                <?= Html::a('Записать на мойку', ['wash/view', 'id' => $model->id, 'Entry[day]' => $entrySearchModel->day], ['class' => 'btn btn-primary btn-sm pull-center', 'style' => 'margin-bottom: 20px']) ?>
            </div>
        </div>
    </div>
</div>