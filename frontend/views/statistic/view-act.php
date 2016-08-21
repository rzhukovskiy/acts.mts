<?php

use yii\widgets\DetailView;

/**
 * @var $this \yii\web\View
 * @var $model \common\models\Act
 */
$this->title = 'Акт';

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php
        echo DetailView::widget([
            'model' => $model,
        ])
        ?>
    </div>
</div>

