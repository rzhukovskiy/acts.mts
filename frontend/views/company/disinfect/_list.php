<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */

use yii\grid\GridView;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список дезинфекций
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                'name',
                'address',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}'
                ],
            ],
        ]);
        ?>
    </div>
</div>