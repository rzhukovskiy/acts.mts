<?php

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\TopicSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $listUser array
 * @var $model common\models\Message
 * @var $type string
 * @var $department_id integer
 */

$this->title = 'Сообщения';

?>
<div class="user-index">
    <?= $this->render('_tabs') ?>

    <div class="panel panel-primary">
        <div class="panel-heading">Сообщения</div>
        <div class="panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'listUser' => $listUser,
            ]); ?>
            
            <?= $this->render('_selector', [
                'type' => $type,
                'department_id' => $department_id,
            ]); ?>
            
            <?= $this->render('_list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]); ?>
        </div>
    </div>
</div>