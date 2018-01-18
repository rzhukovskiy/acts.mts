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
use common\models\TaskUserLink;

$this->title = 'Редактирование задания';

$script = <<< JS
// открываем модальное окно добавить вложения
$('.showFormAttachButt').on('click', function(){
    $('#showFormAttach').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');
JS;
$this->registerJs($script, View::POS_READY);

if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Редактирование', 'url' => ['plan/taskfull'], 'active' => Yii::$app->controller->action->id == 'taskfull'],
    ];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу', 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Редактирование', 'url' => ['plan/taskfull'], 'active' => Yii::$app->controller->action->id == 'taskfull'],
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
                <td class="list-label-md"><?= $model->getAttributeLabel('for_user') ?></td>
                <td>
                    <?php

                    echo Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'for_user',
                        'displayValue' => isset($userLists[$model->for_user]) ? $userLists[$model->for_user] : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'data' => $userListsData,
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) ? false : true,
                        'options' => ['class' => 'form-control'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id]
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $model->getAttributeLabel('for_user_copy') ?></td>
                <td>
                    <?php

                    $users = TaskUserLink::find()->where(['task_id' => $model->id])->select('for_user_copy')->asArray()->column();
                    $userText = '';
                    for ($i = 0; $i < count($users); $i++) {
                        $userText .= $userListsData[$users[$i]] . '<br />';

                    }

                    echo Editable::widget([
                        'model' => $newmodel,
                        'buttonsTemplate' => '{submit}',
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'for_user_copy',
                        'displayValue' => $userText,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'data' => $userListsData,
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) ? false : true,
                        'options' => ['class' => 'form-control', 'multiple' => 'true'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id]
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $model->getAttributeLabel('from_user') ?></td>
                <td>
                    <?php

                    echo Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'from_user',
                        'displayValue' => isset($userLists[$model->from_user]) ? $userLists[$model->from_user] : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'data' => $userListsData,
                        'disabled' => Yii::$app->user->identity->role !== User::ROLE_ADMIN ? true : false,
                        'options' => ['class' => 'form-control'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id]
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
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
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) ? false : true,
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
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
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
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите задание'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
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

                    $pathfolder = \Yii::getAlias('@webroot/files/task/' . $model->id . '/');
                    $shortPath = '/files/task/' . $model->id . '/';

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
                    if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) {
                        echo '<br /><span class="btn btn-primary btn-sm showFormAttachButt" style="margin-right:15px;">Добавить вложение</span>';
                    } else {

                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php
// Модальное окно добавить вложения
$pathfolder = \Yii::getAlias('@webroot/files/task/' . $model->id . '/');
$shortPath = '/files/task/' . $model->id . '/';

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
    'action' => ['/plan/newtendattach', 'id' => $model->id],
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

