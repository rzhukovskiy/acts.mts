<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $type integer common\models\Company
 */

use yii\grid\GridView;

?>
<div class="panel-heading">
    <h3 class="panel-title">Список дезинфекций</h3>
</div>
<div class="row">
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'address',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);
    ?>
</div>