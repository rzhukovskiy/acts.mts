<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CarSearch
 * @var $typeModel \common\models\Type
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'ТС типа «' . Html::encode($typeModel->name) . '»';

?>
<div class="car-count-view">
    <?php if ($admin || !empty(Yii::$app->user->identity->company->children)) { ?>
        <div class="panel panel-primary">
            <div class="panel-heading">
                Поиск
            </div>
            <div class="panel-body">
                <?php echo $this->render('_search', ['model' => $searchModel, 'companyDropDownData' => $companyDropDownData, 'type' => $typeModel->id]); ?>
            </div>
        </div>
    <?php } ?>
    <div class="panel panel-primary">
        <div class="panel-heading"><?= $this->title ?></div>
        <div class="panel-body">
            <?php
            Pjax::begin();
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'id' => 'car-count-view',
                'layout' => "{summary}\n{items}\n{pager}",
                'summary' => false,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'mark.name',
                        'label' => 'Марка',
                    ],
                    [
                        'attribute' => 'number',
                    ],
                    [
                        'attribute' => 'is_infected',
                        'content' => function ($data) {
                            return $data->is_infected ? 'Да' : 'Нет';
                        },
                        'visible' => $admin,
                    ],
                ]
            ]);

            Pjax::end();
            ?>
        </div>
    </div>
</div>
