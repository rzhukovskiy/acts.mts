<?php

/* @var $this \yii\web\View */
/* @var $content string */

use common\models\User;
use kartik\datetime\DateTimePicker;
use kartik\datetime\DateTimePickerAsset;
use kartik\dialog\Dialog;
use kartik\dialog\DialogAsset;
use yii\helpers\Html;
use backend\assets\AppAsset;
use common\widgets\Alert;
use backend\widgets\Menu\menuLeftWidget;
use yii\web\View;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="shortcut icon" href="/favicon.png">
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container-fluid">
        <?php if (!empty(Yii::$app->user->identity->username)) { ?>
            <div class="row top">
                <div class="col-sm-12">
                    <?= Html::a(Yii::$app->user->identity->username,
                        '/site/logout',
                        ['class' => 'btn btn-primary btn-sm pull-right', 'style' => 'margin-right: 10px']) ?>
                    <?php if (Yii::$app->request->cookies->getValue('isAdmin') == '1') : ?>
                        <?= Html::a('Стать админом',
                            ['/user/login', 'id' => 1],
                            ['class' => 'btn btn-danger btn-sm pull-right', 'style' => 'margin-right: 10px']) ?>
                    <?php endif; ?>
                    <?= Html::a('Сменить кабинет',
                        Yii::getAlias('@frontWeb'),
                        ['class' => 'btn btn-primary btn-sm pull-right']) ?>
                </div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-sm-2 content-menu">
                <?= menuLeftWidget::widget() ?>
            </div>
            <div class="col-sm-12 content-main">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
    </div>
</div>

<?php
//ацкий костыль по выводу алертов. не смог придумать более адекватный способ
$action = Yii::$app->controller->action->id;
if (Yii::$app->user->identity->role != User::ROLE_ADMIN && !in_array($action, [
        'update',
        'info',
        'member',
        'driver',
    ])
) {
    DialogAsset::register($this);
    DateTimePicker::widget(['name' => 'asset']);
    Dialog::widget([
        'options' => [],
    ]);
    $this->registerJs("$(document).ready(function() {checkAlerts();});", View::POS_END);
    echo '<audio id="bflat" src="/js/bflat.mp3"></audio>';
}
?>

<?= $this->render('parts/_footer') ?>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
