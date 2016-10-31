<?php

use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $searchModel common\models\search\CompanyDriverSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $searchModel->company->name ?> :: Водители
    </div>
    <div class="panel-body">
        <?= $this->render('/company-driver/_form', [
            'model' => $model,
        ]);
        ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                
                'name',
                'phone',
                'mark.name',
                'type.name',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{delete}',
                    'contentOptions' => ['style' => 'min-width: 70px'],
                ],
            ],
        ]); ?>
    </div>
</div>
