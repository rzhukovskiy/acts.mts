<?php
$this->title = 'Новый почтовый шаблон';
?>

<div class="user-index">
    <?= $this->render('_tabs') ?>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление шаблона
    </div>
    <div class="panel-body">
        <?= $this->render('_add', [
            'model' => $model,
        ]);
        ?>
    </div>
</div>