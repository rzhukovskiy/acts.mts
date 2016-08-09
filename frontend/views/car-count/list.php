<?php

    use yii\grid\GridView;
    use yii\bootstrap\Html;

    /**
     * @var $this \yii\web\View
     * @var $carByTypes \yii\data\ActiveDataProvider
     *
     */

    $this->title = 'Список типов ТС';
    $this->params[ 'breadcrumbs' ][] = $this->title;

?>
<div class="car-count-index">
    <h1><?= $this->title ?></h1>
    <?php

        echo GridView::widget( [
            'dataProvider' => $carByTypes,
            'id' => 'car-count-штвуч',
            'layout' => "{summary}\n{items}\n{pager}",
            'summary' => "Всего: {totalCount}",
            'columns' => [
                [ 'class' => 'yii\grid\SerialColumn' ],
                [
                    'attribute' => 'carsCountByType',
                    'label' => 'Кол-во'
                ],
                [
                    'attribute' => 'type.name',
                    'content' => function ($data) { return Html::a($data->type->name, ['car-count/view', 'type' => $data->type->id]); },
                ],
            ],
        ] );
    ?>

</div>
