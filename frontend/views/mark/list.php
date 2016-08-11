<?php

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
    <h1><?= Html::encode( $this->title ) ?></h1>

    <div class="well">
        <h4>Добавить марку</h4>
        <?= $this->render( '_form', [
            'model' => $model,
        ] ) ?>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">Марки ТС</div>
        <div class="panel-body">
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
    </div>
</div>
