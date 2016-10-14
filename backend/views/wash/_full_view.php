<?php
/**
 * @var $model \common\models\Company
 * @var $modelEntry \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->name ?>  <?= $model->info->address ?>
    </div>
    <div class="panel-body">
        <table class="table table-bordered" style="margin: 0;">
            <tbody>
            <tr>
                <td colspan="2">
                    <label class="control-label">Телефон:</label> <?= $model->info->phone ?>
                </td>
                <td colspan="2">
                    <label class="control-label">Время работы:</label>
                    <?= $model->info->start_at ? date('H:i', $model->info->start_at) : '00:00' ?> - <?= $model->info->end_at ? date('H:i', $model->info->end_at) : '23:00' ?>
                </td>
            </tr>
            <?php
            $arrayFreeTime = $model->getFreeTimeArray($modelEntry->day);
            $i = 0;
            foreach ($arrayFreeTime as $freeTime) {
                if (!$i || !($i % 4)) {
                    echo '<tr class="free-time">';
                }
                echo '<td style="width:25%">' . $freeTime['start'] . ' - ' . $freeTime['end'] . '</td>';
                if ($i + 1 == count($arrayFreeTime)) {
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