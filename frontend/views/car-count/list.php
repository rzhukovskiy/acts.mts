<?php

use yii\bootstrap\Html;
use yii\grid\GridView;

/**
 * @var $this \yii\web\View
 * @var $carByTypes \yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CarSearch
 * @var $companyDropDownData array
 *
 */

$this->title = 'Список типов ТС';

?>
<div class="car-count-index">
    <?php if ($admin) { ?>
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
            echo GridView::widget([
                'dataProvider' => $carByTypes,
                'id' => 'car-count-штвуч',
                'layout' => "{summary}\n{items}\n{pager}",
                'summary' => false,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'type.name',
                        'content' => function ($data) {
                            return $data->type->name;
                        },
                    ],
                    [
                        'attribute' => 'carsCountByType',
                        'label' => 'Кол-во'
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $data, $key) use($searchModel) {
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'type' => $data->type->id, 'CarSearch[company_id]' => $searchModel->company_id]);
                            },
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
