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
$('.showFormAttachmainButt').on('click', function(){
    $('#showFormAttachmain').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');
$('#showFormAttachmain div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');

// Обновление страницы при изменении ID тендера
var refreshPage = false;
$("body").on('DOMSubtreeModified', "#taskuser-tender_id-targ", function() {
    if(refreshPage == false) {
        refreshPage = true;
        $('#taskuser-tender_id-cont').append('<br />Загрузка..');
        location.reload();
    }
});

// открываем модальное окно
$('.putTask').on('click', function() {
    $('#showLists').modal('show');
    
});
JS;
$this->registerJs($script, View::POS_READY);

if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 176)) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Редактирование', 'url' => ['plan/taskfull'], 'active' => Yii::$app->controller->action->id == 'taskfull'],
    ];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу ' . (($countTaskU > 0) ? '<span class="label label-success">' . $countTaskU . '</span>' : ''), 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Редактирование', 'url' => ['plan/taskfull'], 'active' => Yii::$app->controller->action->id == 'taskfull'],
    ];
}

echo Tabs::widget([
    'encodeLabels' => false,
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
                        'disabled' => (Yii::$app->user->identity->role == User::ROLE_ADMIN) ? false : true,
                        'options' => ['class' => 'form-control'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id]
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]);
                    if (Yii::$app->user->identity->id == $model->for_user) {
                        echo '<span class="btn btn-danger btn-sm putTask">Передать задачу</span>';
                    }

                    ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md"><?= $newmodel->getAttributeLabel('for_user_copy') ?></td>
                <td>
                    <?php

                    $users = TaskUserLink::find()->where(['task_id' => $model->id])->select('for_user_copy')->asArray()->column();
                    $userText = '';
                    for ($i = 0; $i < count($users); $i++) {
                        $userText .= $userListsAll[$users[$i]] . '<br />';

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
                        'data' => $userListsAll,
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
                    <?= $model->getAttributeLabel('title') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'title',
                        'displayValue' => $model->title,
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите тему'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('task') ?></td>
                <td style='word-break: break-all;'>
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
                    <?= $model->getAttributeLabel('comment') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'comment',
                        'displayValue' => nl2br($model->comment),
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->for_user)) ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('comment_main') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'comment_main',
                        'displayValue' => nl2br($model->comment_main),
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'disabled' => ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->from_user)) ? false : true,
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('comment_watcher') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXTAREA,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'comment_watcher',
                        'displayValue' => nl2br($model->comment_watcher),
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('tender_id') ?></td>
                <td>
                    <?= Editable::widget([
                        'model' => $model,
                        'buttonsTemplate' => '{submit}',
                        'inputType'       => Editable::INPUT_TEXT,
                        'submitButton' => [
                            'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                        ],
                        'attribute' => 'tender_id',
                        'displayValue' => ($model->tender_id > 0) ? $model->tender_id : '',
                        'asPopover' => true,
                        'placement' => PopoverX::ALIGN_LEFT,
                        'disabled' => ((\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) || ((!$model->tender_id) && ($model->tender_id == 0))) ? false : true,
                        'size' => 'lg',
                        'options' => ['class' => 'form-control', 'placeholder' => 'Введите ID тендера', 'type' => 'number'],
                        'formOptions' => [
                            'action' => ['/plan/taskupdate', 'id' => $model->id],
                        ],
                        'valueIfNull' => '<span class="text-danger">не задано</span>',
                    ]); ?>
                </td>
            </tr>
            <?php

                // Выбор ответственного за разработку тех. задания
                if($model->tender_id) {

                    $modelTender = \common\models\Tender::findOne(['id' => $model->tender_id]);

                    if (isset($modelTender)) {

                        ?>
                        <tr>
                            <td class="list-label-md"><?= $modelTender->getAttributeLabel('work_user_id') ?></td>
                            <td>
                                <?php

                                $workUserArr = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['OR', ['department_id' => 1], ['department_id' => 7]])->select('user.id, user.username')->asArray()->all();

                                $workUserData = [];

                                if (count($workUserArr) > 0) {
                                    $workUserData[''] = '- Выберите разработчика тех. задания';
                                }

                                foreach ($workUserArr as $name => $value) {
                                    $index = $value['id'];
                                    $workUserData[$index] = trim($value['username']);
                                }
                                asort($workUserData);

                                echo Editable::widget([
                                    'model' => $modelTender,
                                    'buttonsTemplate' => '{submit}',
                                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                    'submitButton' => [
                                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                                    ],
                                    'attribute' => 'work_user_id',
                                    'displayValue' => isset($workUserData[$modelTender->work_user_id]) ? ($modelTender->work_user_id > 0 ? $workUserData[$modelTender->work_user_id] : '') : '',
                                    'asPopover' => true,
                                    'placement' => PopoverX::ALIGN_LEFT,
                                    'disabled' => ((\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) || ((!$modelTender->work_user_id) && ($modelTender->tender_close == 0))) ? false : true,
                                    'size' => 'lg',
                                    'data' => $workUserData,
                                    'options' => ['class' => 'form-control'],
                                    'formOptions' => [
                                        'action' => ['/company/updatetender', 'id' => $modelTender->id]
                                    ],
                                    'valueIfNull' => '<span class="text-danger">не задано</span>',
                                ]); ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            ?>
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
            <tr>
                <td class="list-label-md">
                    <?= $model->getAttributeLabel('files_main') ?></td>
                <td>
                    <?php

                    $pathfoldermain = \Yii::getAlias('@webroot/files/task_main/' . $model->id . '/');
                    $shortPathmain = '/files/task_main/' . $model->id . '/';

                    if (file_exists($pathfoldermain)) {

                        $numFiles = 0;
                        $resLinksFiles = '';
                        $arrStateID = [];

                        foreach (\yii\helpers\FileHelper::findFiles($pathfoldermain) as $file) {

                            $resLinksFiles .= Html::a(basename($file), $shortPathmain . basename($file), ['target' => '_blank']) . '<br />';
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
                    if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == $model->for_user)) {
                        echo '<br /><span class="btn btn-primary btn-sm showFormAttachmainButt" style="margin-right:15px;">Добавить вложение</span>';
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
    'header' => '<h5>Добавить вложения инициатора</h5>',
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

<?php
// Модальное окно добавить вложения ответственного
$pathfoldermain = \Yii::getAlias('@webroot/files/task_main/' . $model->id . '/');
$shortPathmain = '/files/task_main/' . $model->id . '/';

$modalAttachmain = Modal::begin([
    'header' => '<h5>Добавить вложения ответственного</h5>',
    'id' => 'showFormAttachmain',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div style='font-size: 15px; margin-left:15px;'>Выберите файлы:</div>";

$modelAddAttachmain = new \yii\base\DynamicModel(['files_main']);
$modelAddAttachmain->addRule(['files_main'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

$form = ActiveForm::begin([
    'action' => ['/plan/newtaskattach', 'id' => $model->id],
    'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '<div class="col-sm-6">{input}</div>',
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]);

echo $form->field($modelAddAttachmain, 'files_main[]')->fileInput(['multiple' => true]);

echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']);

ActiveForm::end();

Modal::end();
// Модальное окно добавить вложения

// Модальное окно передача задачи
$modalLists = Modal::begin([
    'header' => '<h5>Кому передаете задачу</h5>',
    'id' => 'showLists',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-sm',
]);

$form = ActiveForm::begin([
    'action' => ['/plan/put-task', 'id' => $model->id],
    'options' => ['accept-charset' => 'UTF-8'],
]);

echo Html::dropDownList("Plan[for_user]", 'for_user', $userListsData, ['class' => 'form-control', 'style' => 'width:250px;', 'prompt' => 'Выберите пользователя']);

echo Html::submitButton('Передать', ['class' => 'btn btn-primary btn-sm', 'style' => 'margin-top: 10px;']);

ActiveForm::end();
?>
