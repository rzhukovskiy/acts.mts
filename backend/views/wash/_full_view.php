<?php
/**
 * @var $model \common\models\Company
 * @var $modelEntry \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Запись на мойку <?= $model->name ?>
    </div>
    <div class="panel-body">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>
                    <label class="control-label">Адрес:</label> <?= $model->info->address ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="control-label">Телефон:</label> <?= $model->info->phone ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="control-label">Время работы:</label> <?= date('H:i', $model->info->start_at) ?> - <?= date('H:i', $model->info->end_at) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="control-label">Свободное время:</label>
                    <div class="free-time"  style="column-count: 3">
                        <?php
                        $step = 0;
                        $listEntry = $model->getFreeTimeArray($modelEntry->day);
                        $timeStart = gmdate('H:i', $model->info->start_at);
                        $timeEnd = gmdate('H:i', $model->info->end_at);
                        foreach ($listEntry as $entry) {
                            if (!$step) {
                                if (date('H:i', $entry->start_at) != $timeStart) {
                                    echo $timeStart . '&nbsp;-&nbsp;' . date('H:i', $entry->start_at) . '<br />';
                                }
                            } else {
                                echo date('H:i', $entry->start_at) . '<br />';
                            }
                            $step++;
                            if ($step == count($listEntry)) {
                                if (date('H:i', $entry->end_at) != $timeEnd) {
                                    echo date('H:i', $entry->end_at) . '&nbsp;-&nbsp' . $timeEnd . '<br />';
                                } else {
                                    echo '<br />';
                                }
                            } else {
                                echo date('H:i', $entry->end_at) . ' - ';
                            }
                        } ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <?= $this->render('/entry/_form', [
            'model' => $modelEntry,
        ]); ?>
    </div>
</div>