<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        Произошла ошибка. Свяжитесь со службой поддержки (it.artem@mtransservice.ru) и не забудьте сообщить ссылку на страницу где произошла ошибка, ваш логин и коротко рассказать о вашей проблеме!
    </p>

</div>
