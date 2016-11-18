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
        <td><?= $model->phone ?></td>
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
