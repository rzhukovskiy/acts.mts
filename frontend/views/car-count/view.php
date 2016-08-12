<?php

use yii\bootstrap\Html;
use yii\grid\GridView;

/**
 * @var $this \yii\web\View
 * @var $provider \yii\data\ActiveDataProvider
 * @var $typeModel \common\models\Type
 */

$this->title = 'ТС типа «' . Html::encode($typeModel->name) . '»';
$this->params['breadcrumbs'][] = ['label' => 'Типы ТС', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="car-count-view">
    <div class="panel panel-primary">
        <div class="panel-heading"><?= $this->title ?></div>
        <div class="panel-body">
            <?php
            echo GridView::widget([
                'dataProvider' => $provider,
                'id' => 'car-count-view',
                'layout' => "{summary}\n{items}\n{pager}",
                'summary' => false,
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'number',
                    ],
                    [
                        'attribute' => 'mark.name',
                        'label' => 'Марка',
                    ],
                    [
                        'attribute' => 'is_infected',
                        'content' => function ($data) {
                            return $data->is_infected ? 'Да' : 'Нет';
                        },
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>
