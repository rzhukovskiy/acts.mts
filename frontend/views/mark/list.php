<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $model \common\models\Mark
 * @var $searchModel common\models\search\MarkSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $admin boolean
 */

$this->title = 'Марки';

echo $this->render('_tabs');
?>
<div class="mark-list">
    <?php if ($admin) { ?>
        <div class="panel panel-primary">
            <div class="panel-heading">Добавить марку</div>
            <div class="panel-body">
                <?= $this->render('_form',
                [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    <?php } ?>
    <div class="panel panel-primary">
        <div class="panel-heading">Марки ТС</div>
        <div class="panel-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel'  => $searchModel,
                'summary'      => false,
                'columns'      => [
                    [
                        'header' => '№',
                        'class'  => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'name',
                        'content'   => function ($data) {
                            return Html::a($data->name, ['/mark/update', 'id' => $data->id]);
                        },
                    ],
                    [
                        'class'    => 'yii\grid\ActionColumn',
                        'template' => '{update}{delete}',
                        'visible' => $admin,
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
