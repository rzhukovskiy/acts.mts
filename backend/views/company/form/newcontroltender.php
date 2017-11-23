<?php

use yii\bootstrap\Tabs;

$this->title = 'Добавление нового';

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление нового
    </div>
    <div class="panel-body">
        <?= $this->render('_newcontroltender', [
            'model' => $model,
            'usersList' => $usersList,
        ]);
        ?>
    </div>
</div>