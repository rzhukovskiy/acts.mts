<?php
/**
 * @var $model \common\models\Company
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $searchModel \common\models\search\CompanySearch
 */

use yii\bootstrap\Html;

?>
<div class="col-sm-4">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <?= $model->name ?>
            <?php $companyTime = $model->getCompanyTimeByDay($entrySearchModel->day)?>
            <span class="work-time">
                <?= $companyTime ? $companyTime : 'Выходной' ?>
            </span>
        </div>
        <div class="panel-body">
            <div class="col-sm-12" style="margin-top: 15px; font-size: larger">
                <?= $model->fullAddress ?>
            </div>
            <div class="col-sm-12" style="margin-top: 15px; font-size: larger">
                Телефон: <?= $model->info->phone ?>
            </div>
            <div class="free-time" style ="height: 220px; text-align: center;">
                <?php
                $arrayFreeTime = $model->getFreeTimeArray($entrySearchModel->day);
                foreach ($arrayFreeTime as $freeTime) {
                    echo '<div class="col-sm-12">' . $freeTime['start'] . ' - ' .
                        (isset($freeTime['end']) ? $freeTime['end'] : '24:00') . '</div>';
                } ?>
            </div>
            <div class="col-sm-12 text-center">
                <?= Html::a('Записать', [
                    'order/view',
                    'id' => $model->id,
                    'type' => $model->type,
                    'Entry[day]' => $entrySearchModel->day,
                    'card_number' => $searchModel->card_number
                ], ['class' => 'btn btn-primary btn-sm pull-center', 'style' => 'margin-bottom: 20px']) ?>
            </div>
        </div>
    </div>
</div>