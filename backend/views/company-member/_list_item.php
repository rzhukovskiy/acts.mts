<?php

use common\models\CompanyMember;
use yii\bootstrap\Html;

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
        <td><?= $model->email ?></td>
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
