<?php

use \common\models\Company;
use \common\models\Car;

\frontend\assets\WpaintAsset::register($this);

$this->title = 'Детализация штрафа №' . $model->postNumber;

$timenow = time();

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Детализация штрафа №' . $model->postNumber ?>
    </div>
    <div class="panel-body">
        <table class="table table-bordered list-data" style="font-size:13px;">
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('car_id') ?></b></td>
                <td><?php

                    $carRes = Car::find()->leftJoin('mark', '`mark`.`id` = `car`.`mark_id`')->where(['car.id' => $model->car_id])->select('number, mark.name as mark')->asArray()->all();
                    echo $carRes[0]['mark'] . ' - ' . $carRes[0]['number'];

                    ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('company_id') ?></b></td>
                <td><?php

                    $companyRes = Company::find()->where(['id' => $model->company_id])->select('name')->asArray()->column();
                    echo $companyRes[0];

                    ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('description') ?></b></td>
                <td><?= $model->description ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('postNumber') ?></b></td>
                <td><?= $model->postNumber ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('postedAt') ?></b></td>
                <td><?= $model->postedAt ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('violationAt') ?></b></td>
                <td><?= $model->violationAt ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('amount') ?></b></td>
                <td><?= $model->amount ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('discountSize') ?></b></td>
                <td><?php

                    if(isset($model->discountDate)) {

                        if($model->discountDate) {

                            if (strtotime($model->discountDate) > $timenow) {
                                echo $model->discountSize . '%';
                            } else {
                                echo '-';
                            }

                        } else {
                            echo '-';
                        }

                    } else {
                        echo '-';
                    }

                    ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('totalAmount') ?></b></td>
                <td><?php

                    if(isset($model->discountDate)) {

                        if($model->discountDate) {

                            if (strtotime($model->discountDate) > $timenow) {
                                echo $model->totalAmount;
                            } else {
                                echo $model->amount;
                            }

                        } else {
                            echo $model->amount;
                        }

                    } else {
                        echo $model->amount;
                    }

                    ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('discountDate') ?></b></td>
                <td><?php

                    if(isset($model->discountDate)) {

                        if($model->discountDate) {

                            $dataDisc = strtotime($model->discountDate);

                            if ($dataDisc > $timenow) {

                                $discontDate = '';
                                $lostDate = $dataDisc - $timenow;

                                $days = ((Int) ($lostDate / 86400));
                                $lostDate -= (((Int) ($lostDate / 86400)) * 86400);

                                if($days < 0) {
                                    $days = 0;
                                }

                                $hours = (round($lostDate / 3600));
                                $lostDate -= (round($lostDate / 3600) * 3600);

                                if($hours < 0) {
                                    $hours = 0;
                                }

                                /*$minutes = (round($lostDate / 60));

                                if($minutes < 0) {
                                    $minutes = 0;
                                }*/

                                $discontDate .= 'Дней: ' . $days;
                                $discontDate .= ', часов: ' . $hours;
                                //$discontDate .= ', минут: ' . $minutes;

                                echo $discontDate;

                            } else {
                                echo '-';
                            }

                        } else {
                            echo '-';
                        }

                    } else {
                        echo '-';
                    }

                    ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('isExpired') ?></b></td>
                <td><?= ($model->isExpired) ? 'Да' : 'Нет' ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('penaltyDate') ?></b></td>
                <td><?php

                    $penaltyDate = $model->penaltyDate;

                        if(strtotime($penaltyDate) > $timenow) {
                            echo '<u>' . $penaltyDate . '</u>';
                        } else {
                            echo '<span class="text-danger">' . $penaltyDate . '</span>';
                        }?> (Дата, после которой штраф будет передан в службу ФССП)</td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('isPaid') ?></b></td>
                <td><?= ($model->isPaid) ? 'Да' : 'Нет' ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('docType') ?></b></td>
                <td><?= ($model->docType == 'sts') ? 'СТС' : 'Водительское удостоверение' ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('docNumber') ?></b></td>
                <td><?= ($model->docNumber) ? $model->docNumber : '-' ?></td>
            </tr>
            <tr>
                <td class="list-label-md"><b><?= $model->getAttributeLabel('pics') ?></b></td>
                <td><?php

                    if($model->enablePics > 0) {

                        $arrImg = explode(', ', $model->pics);

                        if(count($arrImg) > 0) {

                            $resImg = '';

                            for ($n = 0; $n < count($arrImg); $n++) {
                                $resImg .= '<a href="' . $arrImg[$n] . '" target="_blank"><img width="150px" src="' . $arrImg[$n] . '" /></a>';
                            }

                            echo $resImg;

                        } else {
                            echo '-';
                        }

                    } else {
                        echo '-';
                    }

                    ?></td>
            </tr>
        </table>
    </div>
</div>
