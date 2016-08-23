<?php
use yii\bootstrap\Html;
use yii\grid\GridView;

/**
 * @var $this yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $newTypeModel \common\models\Type
 * @var $searchModel \common\models\search\TypeSearch;
 */

$this->title = 'Типы ТС';

echo $this->render('_tabs');
?>
<div class="image-list">
    <div class="panel panel-primary">
        <div class="panel-heading">Добавить тип</div>
        <div class="panel-body">
            <?= $this->render('_form', ['model' => $newTypeModel]) ?>
        </div>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading">Типы ТС</div>
        <div class="panel-body">
            <?php
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'summary' => false,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'name',
                        'content' => function ($data) {
                            return Html::a($data->name, ['/type/update', 'id' => $data->id]);
                        }
                    ],
                    [
                        'attribute' => 'image',
                        'content' => function ($data) {
                            return Html::img('/images/cars/' . $data->id . '.jpg', ['style' => 'height:100px;']);
                        },
                        'filter' => false,
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{delete}',
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>