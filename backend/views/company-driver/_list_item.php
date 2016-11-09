<?php

use common\models\CompanyDriver;
use yii\bootstrap\Html;

/* @var $this yii\web\View
 * @var $model CompanyDriver
 */
?>
<table class="table table-bordered">
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('name')?></td>
        <td><?= $model->name ?></td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('phone')?></td>
        <td><?= $model->phone ?></td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('mark_id')?></td>
        <td><?= $model->mark ?></td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('type_id')?></td>
        <td><?= $model->type ?></td>
    </tr>
    <tr>
        <td class="list-label-sm"></td>
        <td>
            <div class="form-group">
                <?= Html::a('Удалить', ['company-driver/delete', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </td>
    </tr>
</table>
