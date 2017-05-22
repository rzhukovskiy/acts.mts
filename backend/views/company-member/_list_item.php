<?php

use common\models\CompanyMember;
use yii\bootstrap\Html;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\bootstrap\Modal;

$script = <<< JS

// Обновляем страницу если был изменен номер сотрудника
var numPencil = $(".phoneBody .glyphicon-pencil").length;

$('.phoneBody').bind("DOMSubtreeModified",function(){
    if($(".phoneBody .glyphicon-pencil").length != numPencil) {
      location.reload();
    }
});

JS;
$this->registerJs($script, \yii\web\View::POS_READY);


/* @var $this yii\web\View
 * @var $model CompanyMember
 */
?>
<table class="table table-bordered">
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('name')?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'name[' . $model->id . ']',
                'displayValue' => $model->name,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите фио'],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('position')?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'position[' . $model->id . ']',
                'displayValue' => $model->position,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите должность'],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('phone')?></td>
        <td class="phoneBody">
            <?php foreach (explode(',', $model->phone) as $phone) {
                $phone = trim($phone);
                $code = Yii::$app->user->identity->code;
                echo "<a onclick='callNumber(" .$phone . ");return false;'>$phone</a><br />";
            } ?>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'phone[' . $model->id . ']',
                'asPopover' => true,
                'displayValue' => isset($model->phone) ? '<span class="glyphicon glyphicon-pencil"></span>' : '',
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите номер телефона'],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-sm"><?= $model->getAttributeLabel('email')?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'email[' . $model->id . ']',
                'displayValue' => $model->email,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_RIGHT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите электронную почту'],
                'formOptions' => [
                    'action' => ['/company/updatemember', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
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
