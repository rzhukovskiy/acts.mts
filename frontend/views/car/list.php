<?php

    use yii\grid\GridView;
    use yii\helpers\Html;
    use yii\widgets\Pjax;

    /**
     * @var $this yii\web\View
     * @var $searchModel common\models\search\CarSearch
     * @var $dataProvider yii\data\ActiveDataProvider
     */

    $this->title = 'Cars';
    $this->params[ 'breadcrumbs' ][] = $this->title;

    echo $this->render('_tabs');
?>
<div class="car-index">

    <h1><?= Html::encode( $this->title ) ?></h1>

    <?php Pjax::begin(); ?>    <?= GridView::widget( [
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [ 'class' => 'yii\grid\SerialColumn' ],

            'company.name',
            'number',
            'mark.name',
            'type.name',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
            ],
        ],
    ] ); ?>
    <?php Pjax::end(); ?>
</div>
