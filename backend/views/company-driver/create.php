<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = $model->isNewRecord ? 'Добавление водителя' : 'Изменение водителя';

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление водителя' : 'Изменение водителя' ?>
    </div>
    <div class="panel-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]);
        ?>
    </div>
</div>