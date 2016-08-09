<?php

    use yii\bootstrap\Tabs;
    use yii\grid\GridView;
    use yii\helpers\Html;
    use yii\widgets\Pjax;

    /**
     * @var $this yii\web\View
     * @var $model \common\models\Mark
     * @var $searchModel common\models\search\MarkSearch
     * @var $dataProvider yii\data\ActiveDataProvider
     */

    $this->title = 'Марки';
    $this->params[ 'breadcrumbs' ][] = $this->title;
?>
<div class="mark-list">

    <?= Tabs::widget( [
        'items' => [
            [
                'label' => 'Марки ТС',
                'url' => false,
                'active' => true
            ],
            [
                'label' => 'Виды ТС',
                'url' => '/type/list',
            ],
        ]
    ] ) ?>
    <h1><?= Html::encode( $this->title ) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= $this->render( '_form', [
            'model' => $model,
        ] ) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget( [
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [ 'class' => 'yii\grid\SerialColumn' ],
            [
                'attribute' => 'name',
                'content' => function ( $data ) {
                    return Html::a( $data->name, [ '/mark/update', 'id' => $data->id ] );
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'update' => function ( $url ) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-screenshot"></span>',
                            $url );
                    },
                ],
            ],
        ],
    ] ); ?>
    <?php Pjax::end(); ?>
</div>
