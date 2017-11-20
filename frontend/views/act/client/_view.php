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
                <td><?= isset($model->card->number) ? $model->card->number : '' ?></td>
                <td><?= $model->mark->name ?></td>
                <td colspan="2"><?= $model->car_number ?></td>
                <td><?= $model->partner->address ?></td>
            </tr>

            <?php if($model->service_type == 3) { ?>
                <tr class="header">
                    <td colspan="3">Запасные части</td>
                    <td>Стоимость</td>
                    <td>Кол-во</td>
                    <td>Сумма</td>
                </tr>

                <?php $num = 1;
                $numAmount = 0;
                $total = 0;
                foreach ($model->getClientScopes()->all() as $scope) { ?>

                    <?php if($scope->parts == 1) { ?>
                        <tr>
                            <td colspan="3"><?= $num . '. ' . $scope->description ?></td>
                            <td><?= $scope->price ?></td>
                            <td><?= $scope->amount ?></td>
                            <td><?= $scope->price * $scope->amount ?></td>
                        </tr>
                        <?php $num++; $numAmount += $scope->amount;
                        $total += $scope->price * $scope->amount;
                    } ?>
                <?php } ?>

                <tr class="strong">
                    <td colspan="3">Всего</td>
                    <td></td>
                    <td><?php

                        if($model->service_type == 3) {
                            echo $numAmount;
                        } else {
                            echo --$num;
                        }

                        ?></td>
                    <td><?= $total ?></td>
                </tr>

                <tr class="header">
                    <td colspan="3">Услуги</td>
                    <td>Стоимость</td>
                    <td>Кол-во</td>
                    <td>Сумма</td>
                </tr>

                <?php $num = 1;
                $numAmountService = 0;
                $totalService = 0;
                foreach ($model->getClientScopes()->all() as $scope) { ?>

                    <?php if($scope->parts == 0) { ?>
                        <tr>
                            <td colspan="3"><?= $num . '. ' . $scope->description ?></td>
                            <td><?= $scope->price ?></td>
                            <td><?= $scope->amount ?></td>
                            <td><?= $scope->price * $scope->amount ?></td>
                        </tr>
                        <?php $num++; $numAmountService += $scope->amount;
                        $totalService += $scope->price * $scope->amount;
                    } ?>
                <?php } ?>

                <tr class="strong">
                    <td colspan="3">Всего</td>
                    <td></td>
                    <td><?= $numAmountService ?></td>
                    <td><?= $totalService ?></td>
                </tr>
                <tr class="strong">
                    <td colspan="3">Итого</td>
                    <td></td>
                    <td><?= ($numAmount + $numAmountService) ?></td>
                    <td><?= ($total + $totalService) ?></td>
                </tr>
            <?php } else { ?>
                <tr class="header">
                    <td colspan="3">Вид услуг</td>
                    <td>Стоимость</td>
                    <td>Кол-во</td>
                    <td>Сумма</td>
                </tr>

                <?php $num = 1;
                $numAmount = 0;
                $total = 0;
                foreach ($model->getClientScopes()->all() as $scope) { ?>
                    <tr>
                        <td colspan="3"><?= $num . '. ' . $scope->description ?></td>
                        <td><?= $scope->price ?></td>
                        <td><?= $scope->amount ?></td>
                        <td><?= $scope->price * $scope->amount ?></td>
                    </tr>
                    <?php $num++; $numAmount += $scope->amount;
                    $total += $scope->price * $scope->amount;
                } ?>

                <tr class="strong">
                    <td colspan="3">Итого</td>
                    <td></td>
                    <td><?= $numAmount ?></td>
                    <td><?= $total ?></td>
                </tr>
            <?php } ?>
        </table>

        <?php /*<div class="form-group" style="margin-top: 20px;">
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
                        <?php if (file_exists('files/checks/' . $model->id . '-name.png')) { ?><img
                            style="width:250px; border-bottom: 1px solid black;"
                            src="<?= '/files/checks/' . $model->id . '-name.png' ?>"/><?php } ?>
                    </td>
                    <td>
                        &nbsp; &nbsp; &nbsp;
                    </td>
                    <td>
                        Подпись водителя
                    </td>

                    <td colspan="2">
                        <?php if (file_exists('files/checks/' . $model->id . '-sign.png')) { ?><img
                            style="width:250px; border-bottom: 1px solid black;"
                            src="<?= '/files/checks/' . $model->id . '-sign.png' ?>"/><?php } ?>
                    </td>
                </tr>
            </table>
        </div> */?>
    </div>
</div>