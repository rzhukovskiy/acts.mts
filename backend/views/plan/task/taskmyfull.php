<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use yii\bootstrap\Tabs;
use common\models\User;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\web\View;

$this->title = 'Редактирование собственного задания';

$script = <<< JS
// открываем модальное окно добавить вложения
$('.showFormAttachButt').on('click', function(){
    $('#showFormAttach').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');
JS;
$this->registerJs($script, View::POS_READY);

if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 176)) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу', 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Редактирование', 'url' => ['plan/taskmyfull'], 'active' => Yii::$app->controller->action->id == 'taskmyfull'],
    ];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу', 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Редактирование', 'url' => ['plan/taskmyfull'], 'active' => Yii::$app->controller->action->id == 'taskmyfull'],
    ];
}

echo Tabs::widget([
    'items' => $tabs,
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование задания' ?>
    </div>
    <div class="panel-body">
        <table class="table table-bordered list-data">
            <tr>
                <td class="list-label-md"><?= $model->getAttributeLabel('data') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'data',
                        'displayValue' => $model->data ? date('d.m.Y H:i', $model->data) : '',
                        'inputType' => Editable::INPUT_DATETIME,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => [
                            'options' => ['value' => $model->data ? date('d.m.Y H:i', $model->data) : ''],
                            'class' => 'form-control',
                            'removeButton' => false,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy hh:i',
                                'weekStart'=>1,
                                'autoclose' => true,
                                'pickerPosition' => 'bottom-right',
                            ],
                        ],
                        'formOptions' => [
                            'action' => ['/plan/taskmyupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('task') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'task',
                        'displayValue' => nl2br($model->task),
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите задание'],
                        'formOptions' => [
                            'action' => ['/plan/taskmyupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('files') ?></td>
                <td>
                    <?php

                    $pathfolder = \Yii::getAlias('@webroot/files/mytask/' . $model->id . '/');
                    $shortPath = '/files/mytask/' . $model->id . '/';

                    if (file_exists($pathfolder)) {

                        $numFiles = 0;
                        $resLinksFiles = '';
                        $arrStateID = [];

                        foreach (\yii\helpers\FileHelper::findFiles($pathfolder) as $file) {

                            $resLinksFiles .= Html::a(basename($file), $shortPath . basename($file), ['target' => '_blank']) . '<br />';
                            $numFiles++;

                        }

                        if($numFiles > 0) {
                            echo $resLinksFiles;
                        } else {
                            echo '-<br />';
                        }

                    } else {
                        echo '-<br />';
                    }

                    ?>

                    <?php
                        echo '<br /><span class="btn btn-primary btn-sm showFormAttachButt" style="margin-right:15px;">Добавить вложение</span>';
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php
// Модальное окно добавить вложения
$pathfolder = \Yii::getAlias('@webroot/files/mytask/' . $model->id . '/');
$shortPath = '/files/mytask/' . $model->id . '/';

$modalAttach = Modal::begin([
    'header' => '<h5>Добавить вложения</h5>',
    'id' => 'showFormAttach',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div style='font-size: 15px; margin-left:15px;'>Выберите файлы:</div>";

$modelAddAttach = new \yii\base\DynamicModel(['files']);
$modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

$form = ActiveForm::begin([
    'action' => ['/plan/taskmyattach', 'id' => $model->id],
    'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '<div class="col-sm-6">{input}</div>',
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]);

echo $form->field($modelAddAttach, 'files[]')->fileInput(['multiple' => true]);

echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']);

ActiveForm::end();

Modal::end();
// Модальное окно добавить вложения
?>

