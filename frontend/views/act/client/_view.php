<?php
/**
 * @var $this yii\web\View
 * @var $model \common\models\Act
 */
?>
<div class="panel panel-primary acts">
    <div class="panel-heading">
        Предварительный акт
    </div>
    <div class="panel-body">
        <table cellspacing="0" cellpadding="0" border="0" class="act">
            <tr>
                <th>Дата</th>
                <th>№ Карты</th>
                <th>Марка ТС</th>
                <th colspan="2">Госномер</th>
                <th>Город</th>
            </tr>
            <tr class="strong">
                <td><?= date("d.m.Y", $model->served_at) ?></td>
                <td><?= $model->card->number ?></td>
                <td><?= $model->mark->name ?></td>
                <td colspan="2"><?= $model->number ?></td>
                <td><?= $model->partner->address ?></td>
            </tr>

            <tr class="header">
                <td colspan="3">Вид услуг</td>
                <td>Кол-во</td>
                <td>Стоимость</td>
                <td>Сумма</td>
            </tr>

            <?php $num = 1;
            $total = 0;
            foreach ($model->getClientScopes()->all() as $scope) { ?>
                <tr>
                    <td colspan="3"><?= $num . '. ' . $scope->description ?></td>
                    <td><?= $scope->amount ?></td>
                    <td><?= $scope->price ?></td>
                    <td><?= $scope->price * $scope->amount ?></td>
                </tr>
                <?php $num++;
                $total += $scope->price * $scope->amount;
            } ?>

            <tr class="strong">
                <td colspan="3">Итого</td>
                <td><?= --$num ?></td>
                <td></td>
                <td><?= $total ?></td>
            </tr>
        </table>

        <div class="form-group" style="margin-top: 20px;">
            <span class="sign">
                    По качеству работы претензий не имею.
            </span>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <table class="sign">
                <tr>
                    <td>
                        ФИО водителя
                    </td>

                    <td colspan="2">
                        <?php if (file_exists('/signs/' . $model->id . '-name.png')) { ?><img
                            style="width:250px; border-bottom: 1px solid black;"
                            src="<?= '/signs/' . $model->id . '-name.png' ?>"/><?php } ?>
                    </td>
                    <td>
                        &nbsp; &nbsp; &nbsp;
                    </td>
                    <td>
                        Подпись водителя
                    </td>

                    <td colspan="2">
                        <?php if (file_exists('/signs/' . $model->id . '-sign.png')) { ?><img
                            style="width:250px; border-bottom: 1px solid black;"
                            src="<?= '/signs/' . $model->id . '-sign.png' ?>"/><?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>