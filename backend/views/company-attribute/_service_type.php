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
        Нормо-час на основные виды работ
    </div>
    <div class="panel-body">
        <?php  $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels'  => $model->value,
            'sort'       => [
                'attributes' => ['service_type', 'service_hour_norm'],
            ],
            'pagination' => false,
        ]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout'       => "{items}\n{pager}",
            'columns'      => [
                [
                    'header'    => 'Вид работ',
                    'attribute' => 'service_type',
                ],
                [
                    'header'    => 'Норма часа',
                    'attribute' => 'service_hour_norm',
                ],
            ],
        ]);
        ?>
    </div>
</div>
