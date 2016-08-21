<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use frontend\widgets\Menu\menuLeftWidget;

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
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container-fluid">
        <?php if (!empty(Yii::$app->user->identity->username)) { ?>
            <div class="row top">
                <div class="col-sm-12">
                    <?= Html::a(
                        'Выход (' . Yii::$app->user->identity->username . ')',
                        '/site/logout',
                        ['class' => 'btn btn-primary btn-sm pull-right']
                    ) ?>
                    <?php if(Yii::$app->request->cookies->getValue('isAdmin') == '1') : ?>
                        <?= Html::a('Стать админом', ['/user/login', 'id' => 1], ['class' => 'btn btn-default btn-sm pull-right']) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-sm-2">
                <?= menuLeftWidget::widget() ?>
            </div>
            <div class="col-sm-10">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
    </div>
</div>


<?= $this->render('parts/_footer') ?>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
