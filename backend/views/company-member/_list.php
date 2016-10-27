<?php

use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $searchModel->company->name ?> :: Сотрудники
    </div>
    <div class="panel-body">
        <?= $this->render('/company-member/_form', [
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

                'position',
                'name',
                'phone',
                'email:email',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{delete}',
                    'contentOptions' => ['style' => 'min-width: 70px'],
                ],
            ],
        ]); ?>
    </div>
</div>
