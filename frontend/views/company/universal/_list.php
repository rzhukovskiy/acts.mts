<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список универсальных
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
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