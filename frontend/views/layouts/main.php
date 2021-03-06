<?php

/* @var $this \yii\web\View */
/* @var $content string */

use common\models\User;
use common\widgets\Alert;
use frontend\assets\AppAsset;
use frontend\widgets\Menu\menuLeftWidget;
use yii\helpers\Html;

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
                        ['class' => 'btn btn-primary btn-sm pull-right']) ?>
                    <?php if (Yii::$app->request->cookies->getValue('isAdmin') == '1') : ?>
                        <?= Html::a('Стать админом',
                            ['/user/login', 'id' => 1],
                            ['class' => 'btn btn-danger btn-sm pull-right', 'style' => 'margin-right: 10px']) ?>
                    <?php endif; ?>
                    <?= Yii::$app->user->can(User::ROLE_WATCHER) ? Html::a('Сменить кабинет',
                        Yii::getAlias('@backWeb'),
                        ['class' => 'btn btn-primary btn-sm pull-right', 'style' => 'margin-right: 10px']) : '' ?>
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


<?= $this->render('parts/_footer') ?>
<div class="nav-button">
    <div style="display:none;" class="nav_up" id="nav_up"></div>
    <div style="display:none;" class="nav_down" id="nav_down"></div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
