<?php
/**
 * @var $model \common\models\Company
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $searchModel \common\models\search\CompanySearch
 * @var $entryModel \common\models\Entry
 */

use yii\bootstrap\Html;

?>
<div class="col-sm-4">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <?= $model->name ?>
        </div>
        <div class="panel-body">
            <div class="col-sm-12" style="margin-top: 15px; font-size: larger">
                <?php $companyTime = $model->getCompanyTimeByDay($entrySearchModel->day)?>
            График работы:
            <span class=""><?= $companyTime->start_at ? gmdate('H:i', $companyTime->start_at) : '00:00' ?> - <?= $companyTime->end_at ? gmdate('H:i', $companyTime->end_at) : '24:00' ?></span>
            </div>
            <div class="col-sm-12" style="margin-top: 15px; font-size: larger">
                Адрес: <?= $model->fullAddress ?>
            </div>
            <div class="col-sm-12" style="margin-top: 15px; font-size: larger">
                Телефон: <?= $model->info->phone ?>
            </div>
            <div class="free-time" style ="height: 220px; text-align: center;">
                <?php
                $arrayFreeTime = $model->getFreeTimeArray($entrySearchModel->day);
                foreach ($arrayFreeTime as $freeTime) {
                    echo '<div class="col-sm-12">' . $freeTime['start'] . ' - ' . $freeTime['end'] . '</div>';
                } ?>
            </div>
            <div class="col-sm-12 text-center">
                <?= Html::a('Записать', [
                    'order/view',
                    'id' => $model->id,
                    'type' => $model->type,
                    'Entry[id]' => $entryModel ? $entryModel->id : null,
                    'Entry[day]' => $entrySearchModel->day,
                    'card_number' => $searchModel->card_number,
                ], ['class' => 'btn btn-primary btn-sm pull-center', 'style' => 'margin-bottom: 20px']) ?>
            </div>
        </div>
    </div>
</div>