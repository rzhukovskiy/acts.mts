<?php

use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyAttributes
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Транспортные средстав компании
    </div>
    <div class="panel-body">
        <?php  $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels'  => $model->value,
            'sort'       => [
                'attributes' => ['car_mark', 'car_type', 'car_count'],
            ],
            'pagination' => false,
        ]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout'       => "{items}\n{pager}",
            'columns'      => [
                [
                    'header'    => 'Марка ТС',
                    'attribute' => 'car_mark',
                    'value'     => function ($data) {
                        return \common\models\Mark::getMarkList()[$data['car_mark']];
                    },
                ],
                [
                    'header'    => 'Тип ТС',
                    'attribute' => 'car_type',
                    'value'     => function ($data) {
                        return \common\models\Type::getTypeList()[$data['car_type']];
                    },
                ],
                [
                    'header'    => 'Количество ТС',
                    'attribute' => 'car_count',
                ],
            ],
        ]);
        ?>
    </div>
</div>
