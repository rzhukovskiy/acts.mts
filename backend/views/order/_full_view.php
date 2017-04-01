<?php
/**
 * @var $model \common\models\Company
 * @var $modelEntry \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->name ?>  <span class="work-time"><?= $model->fullAddress ?></span>
    </div>
    <div class="panel-body">
        <table class="table table-bordered" style="margin: 0;">
            <tbody>
            <tr style="font-size: larger">
                <td colspan="2">
                    <label class="control-label">Телефон:</label> <?= $model->info->phone ?>
                </td>
                <td colspan="2">
                    <label class="control-label">Время работы:</label>
                    <?php $companyTime = !empty($searchModel) ? $model->getCompanyTimeByDay($searchModel->day) : false?>
                    <span class="work-time">
                        <?= $companyTime ? $companyTime : 'Выходной' ?>
                    </span>
                </td>
            </tr>
            <?php
            $arrayFreeTime = $model->getFreeTimeArray($modelEntry->day);
            $i = 0;
            foreach ($arrayFreeTime as $freeTime) {
                if (!$i || !($i % 4)) {
                    echo '<tr class="free-time">';
                }
                echo '<td style="width:25%">' . $freeTime['start'] . ' - ' .
                    (isset($freeTime['end']) ? $freeTime['end'] : '24:00') . '</td>';
                if ($i + 1 == count($arrayFreeTime) && count($arrayFreeTime) % 4) {
                    for ($j = 0; $j < 4 - count($arrayFreeTime) % 4; $j++) {
                        echo '<td style="width:25%"></td>';
                    }
                }
                if ($i + 1 == count($arrayFreeTime) || !(($i + 1) % 4)) {
                    echo '</tr>';
                }
                $i++;
            } ?>
            </tbody>
        </table>

        <?= $this->render('/entry/_form', [
            'model' => $modelEntry,
        ]); ?>
    </div>
</div>