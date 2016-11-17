<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $searchModel common\models\search\CompanyDriverSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $searchModel->company->name ?> :: Водители
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['company-driver/create', 'company_id' => $searchModel->company_id], ['class' => 'btn btn-danger btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'options' => [
                'tag' => 'div',
                'class' => 'list-data',
            ],
            'layout' => "{items}",
            'itemView' => '_list_item',
        ]);
        ?>
    </div>
</div>
