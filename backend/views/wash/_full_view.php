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
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>
                    <label class="control-label">Телефон:</label> <?= $model->info->phone ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="control-label">Свободное время:</label>

                    <div class="free-time">
                        <?php
                        $step = 0;
                        $listEntry = $model->getFreeTimeArray(date('d-m-Y'));
                        $timeStart = gmdate('H:i', $model->info->start_at);
                        $timeEnd = gmdate('H:i', $model->info->end_at);
                        foreach ($listEntry as $entry) {
                            if (!$step) {
                                if (date('H:i', $entry->start_at) != $timeStart) {
                                    echo
                                        '<div class="col-sm-3">' .
                                        $timeStart .
                                        ' - ' .
                                        date('H:i', $entry->start_at) .
                                        '</div><div class="col-sm-3">';
                                } else {
                                    echo '<div class="col-sm-3">';
                                }
                            } else {
                                echo date('H:i', $entry->start_at) . '</div><div class="col-sm-3">';
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
                </td>
            </tr>
            </tbody>
        </table>

        <?= $this->render('/entry/_form',
        [
            'model' => $modelEntry,
        ]); ?>
    </div>
</div>