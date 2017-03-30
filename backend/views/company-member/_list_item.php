<?php

use common\models\CompanyMember;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;

/* @var $this yii\web\View
 * @var $model CompanyMember
 */
?>
<table class="table table-bordered">
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('name')?></td>
        <td><?= $model->name ?></td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('position')?></td>
        <td><?= $model->position ?></td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('phone')?></td>
        <td>
            <?php foreach (explode(',', $model->phone) as $phone) {
                $phone = trim($phone);
                $code = Yii::$app->user->identity->code;
                echo "<a href='https://ih392584.vds.myihor.ru/app/click_to_call/click_to_call.php?" .
                    "src_cid_name=$code&src_cid_number=$code&dest_cid_name=&dest_cid_number=&src=$code&dest=$phone" .
                    "&auto_answer=&rec=false&ringback=us-ring'>$phone</a><br />";
            } ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('email')?></td>
        <td>
            <?= $model->email ?>
            <?php
            Modal::begin([
                'header' => '<h2>Отправка письма</h2>',
                'toggleButton' => [
                    'tag' => 'a',
                    'label' => '<span class="glyphicon glyphicon-envelope"></span>',
                    'style' => 'cursor: pointer',
                ],
            ]);

            echo $this->render('_mail', [
                'model' => $model,
            ]);

            Modal::end();
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"></td>
        <td>
            <div class="form-group">
                <?= Html::a('Удалить', ['company-member/delete', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </td>
    </tr>
</table>
