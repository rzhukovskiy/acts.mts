<?php

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\TopicSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Сообщения';

?>
<div class="user-index">
    <?= $this->render('_tabs') ?>

    <div class="panel panel-primary">
        <div class="panel-heading">Сообщения</div>
        <div class="panel-body">
            <?= $this->render('_list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]); ?>
        </div>
    </div>
</div>